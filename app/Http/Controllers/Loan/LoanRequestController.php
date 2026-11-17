<?php

namespace App\Http\Controllers\Loan;

use App\Enums\LoanRequestStatus;
use App\Enums\LoanStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoanRequest\ApproveLoanRequestRequest;
use App\Http\Requests\LoanRequest\RejectLoanRequestRequest;
use App\Http\Requests\LoanRequest\StoreLoanRequestRequest;
use App\Models\Customer;
use App\Models\LoanRequest;
use App\Models\Notification;
use App\Services\Date\JalaliDateService;
use App\Services\LoanType\LoanTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;
use RuntimeException;
use Throwable;

class LoanRequestController extends Controller
{
    private const MIN_LOAN_AMOUNT = 10000000;

    private const MAX_LOAN_AMOUNT = 200000000;

    public function __construct(
        private readonly LoanTypeService $loanTypeService
    ) {
    }

    /**
     * اعتبارسنجی تاریخ شمسی با قالب Y/m/d
     */
    private function isValidJalaliDate(mixed $date): bool
    {
        if (
            ! is_string($date) ||
            ! preg_match('/\A\d{4}\/\d{2}\/\d{2}\z/', $date)
        ) {
            return false;
        }

        try {
            $parsed = Jalalian::fromFormat('Y/m/d', $date);

            return $parsed->format('Y/m/d') === $date;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * لیست درخواست‌ها
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],
            'status' => [
                'nullable',
                'string',
                'in:pending,approved,rejected,cancelled',
            ],
            'from_date' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (! $this->isValidJalaliDate($value)) {
                        $fail('تاریخ شروع معتبر نیست.');
                    }
                },
            ],
            'to_date' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (! $this->isValidJalaliDate($value)) {
                        $fail('تاریخ پایان معتبر نیست.');
                    }
                },
            ],
        ]);

        $search = trim($validated['search'] ?? '');
        $status = $validated['status'] ?? null;
        $fromDate = $validated['from_date'] ?? null;
        $toDate = $validated['to_date'] ?? null;

        if (
            $fromDate &&
            $toDate &&
            $fromDate > $toDate
        ) {
            return redirect()
                ->back()
                ->withErrors([
                    'to_date' => 'تاریخ پایان نمی‌تواند قبل از تاریخ شروع باشد.',
                ])
                ->withInput();
        }

        $dateService = app(JalaliDateService::class);

        $query = LoanRequest::query()
            ->with([
                'customer',
                'loan',
            ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhereRaw(
                                "CONCAT(first_name, ' ', last_name) LIKE ?",
                                ["%{$search}%"]
                            )
                            ->orWhere(
                                'national_code',
                                'like',
                                "%{$search}%"
                            );
                    });
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($fromDate) {
            $query->whereDate(
                'created_at',
                '>=',
                $dateService->toGregorian($fromDate)
            );
        }

        if ($toDate) {
            $query->whereDate(
                'created_at',
                '<=',
                $dateService->toGregorian($toDate)
            );
        }

        $loanRequests = $query
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view(
            'loan_requests.index',
            compact(
                'loanRequests',
                'search',
                'status',
                'fromDate',
                'toDate'
            )
        );
    }

    /**
     * فرم ثبت درخواست توسط مدیر
     */
    public function create()
    {
        $customers = Customer::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view(
            'loan_requests.create',
            compact('customers')
        );
    }

    /**
     * بررسی امکان ثبت درخواست جدید برای مشتری
     */
    private function getCustomerRequestRestriction(Customer $customer): ?string
    {
        if (
            $customer->loans()
                ->where('status', LoanStatus::ACTIVE->value)
                ->exists()
        ) {
            return 'این مشتری دارای وام فعال است.';
        }

        $latestRequest = LoanRequest::query()
            ->with('loan')
            ->where('customer_id', $customer->id)
            ->latest('id')
            ->first();

        if (! $latestRequest) {
            return null;
        }

        if ($latestRequest->status === LoanRequestStatus::PENDING) {
            return 'این مشتری درخواست وام در انتظار بررسی دارد.';
        }

        if ($latestRequest->status === LoanRequestStatus::APPROVED) {
            if (
                ! $latestRequest->loan ||
                $latestRequest->loan->status !== LoanStatus::FINISHED
            ) {
                return 'درخواست تأییدشده قبلی تا تسویه کامل وام مانع ثبت درخواست جدید است.';
            }

            return null;
        }

        if ($latestRequest->status === LoanRequestStatus::REJECTED) {
            if (! $latestRequest->next_review_date) {
                return 'برای درخواست ردشده تاریخ مراجعه مجدد تعیین نشده است.';
            }

            if (
                now()->toDateString() <
                $latestRequest->next_review_date->toDateString()
            ) {
                return 'هنوز تاریخ مجاز ثبت درخواست مجدد فرا نرسیده است.';
            }

            return null;
        }

        if ($latestRequest->status === LoanRequestStatus::CANCELLED) {
            return null;
        }

        return 'وضعیت درخواست قبلی اجازه ثبت درخواست جدید را نمی‌دهد.';
    }

    /**
     * ثبت درخواست توسط مدیر
     */
    public function store(StoreLoanRequestRequest $request)
    {
        $validated = $request->validated();

        try {
            DB::transaction(function () use ($validated) {
                $customer = Customer::query()
                    ->lockForUpdate()
                    ->findOrFail($validated['customer_id']);

                $restriction = $this->getCustomerRequestRestriction(
                    $customer
                );

                if ($restriction) {
                    throw new RuntimeException($restriction);
                }

                LoanRequest::create([
                    'customer_id' => $customer->id,
                    'requested_amount' => $validated['requested_amount'],
                    'description' => $validated['description'] ?? null,
                    'status' => LoanRequestStatus::PENDING,
                ]);
            }, 3);
        } catch (RuntimeException $exception) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('loan-requests.index')
            ->with('success', 'درخواست وام ثبت شد.');
    }

    /**
     * نمایش درخواست
     */
    public function show(LoanRequest $loanRequest)
    {
        $loanRequest->load([
            'customer',
            'customer.user',
            'loanType',
            'loan',
        ]);

        $loanTypes = $this->loanTypeService->getActive();

        return view(
            'loan_requests.show',
            compact('loanRequest', 'loanTypes')
        );
    }

    /**
     * فرم ویرایش درخواست
     */
    public function edit(LoanRequest $loanRequest)
    {
        $loanRequest->load([
            'customer',
            'customer.user',
            'loanType',
            'loan',
        ]);

        $loanTypes = $this->loanTypeService->getActive();

        return view(
            'loan_requests.edit',
            compact('loanRequest', 'loanTypes')
        );
    }

    /**
     * ویرایش اطلاعات اصلی درخواست
     *
     * فقط درخواست در انتظار بررسی قابل ویرایش است.
     * اطلاعات تأیید یا ردشده باید از مسیرهای مخصوص خود تغییر کنند.
     */
    public function update(
        Request $request,
        LoanRequest $loanRequest
    ) {
        $validated = $request->validate([
            'requested_amount' => [
                'bail',
                'required',
                'integer',
                'min:' . self::MIN_LOAN_AMOUNT,
                'max:' . self::MAX_LOAN_AMOUNT,
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        DB::transaction(function () use (
            $loanRequest,
            $validated
        ) {
            $lockedLoanRequest = LoanRequest::query()
                ->lockForUpdate()
                ->findOrFail($loanRequest->id);

            if ($lockedLoanRequest->loan_id) {
                throw new RuntimeException(
                    'این درخواست به وام متصل شده و امکان ویرایش آن وجود ندارد.'
                );
            }

            if (
                $lockedLoanRequest->status !==
                LoanRequestStatus::PENDING
            ) {
                throw new RuntimeException(
                    'فقط درخواست‌های در انتظار بررسی قابل ویرایش هستند.'
                );
            }

            $lockedLoanRequest->update([
                'requested_amount' => $validated['requested_amount'],
                'description' => $validated['description'] ?? null,
            ]);
        }, 3);

        return redirect()
            ->route('loan-requests.show', $loanRequest)
            ->with('success', 'اطلاعات درخواست وام با موفقیت ویرایش شد.');
    }

    /**
     * تأیید درخواست
     */
    public function approve(
        ApproveLoanRequestRequest $request,
        LoanRequest $loanRequest
    ) {
        $validated = $request->validated();

        DB::transaction(function () use (
            $loanRequest,
            $validated
        ) {
            $lockedLoanRequest = LoanRequest::query()
                ->lockForUpdate()
                ->with('customer.user')
                ->findOrFail($loanRequest->id);

            if (
                $lockedLoanRequest->status !==
                LoanRequestStatus::PENDING
            ) {
                abort(422, 'این درخواست قبلاً بررسی شده است.');
            }

            if ($lockedLoanRequest->loan_id) {
                abort(422, 'برای این درخواست قبلاً وام ایجاد شده است.');
            }

            $user = $lockedLoanRequest->customer?->user;

            $lockedLoanRequest->update([
                'status' => LoanRequestStatus::APPROVED,
                'approved_amount' => $validated['approved_amount'],
                'loan_type_id' => $validated['loan_type_id'],
                'approved_installment_count' =>
                    $validated['approved_installment_count'],
                'approved_installment_interval' =>
                    $validated['approved_installment_interval'],
                'review_note' => $validated['review_note'],
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'next_review_date' => null,
            ]);

            if ($user) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'loan_request_approved',
                    'title' => 'درخواست وام تأیید شد',
                    'message' =>
                        'درخواست وام شما با مبلغ ' .
                        fa_money($validated['approved_amount']) .
                        ' تأیید شد.',
                    'data' => [
                        'loan_request_id' => $lockedLoanRequest->id,
                        'approved_amount' => $validated['approved_amount'],
                        'approved_installment_count' =>
                            $validated['approved_installment_count'],
                        'approved_installment_interval' =>
                            $validated['approved_installment_interval'],
                        'review_note' => $validated['review_note'],
                    ],
                ]);
            }
        }, 3);

        return redirect()
            ->back()
            ->with('success', 'درخواست وام تأیید شد.');
    }

    /**
     * رد درخواست
     */
    public function reject(
        RejectLoanRequestRequest $request,
        LoanRequest $loanRequest
    ) {
        $validated = $request->validated();

        DB::transaction(function () use (
            $loanRequest,
            $validated
        ) {
            $lockedLoanRequest = LoanRequest::query()
                ->lockForUpdate()
                ->with('customer.user')
                ->findOrFail($loanRequest->id);

            if (
                $lockedLoanRequest->status !==
                LoanRequestStatus::PENDING
            ) {
                abort(422, 'این درخواست قبلاً بررسی شده است.');
            }

            if ($lockedLoanRequest->loan_id) {
                abort(422, 'برای این درخواست قبلاً وام ایجاد شده است.');
            }

            $nextReviewDate = null;
            $nextReviewDateJalali = null;

            if (! empty($validated['next_review_date'])) {
                $nextReviewDateJalali =
                    $validated['next_review_date'];

                $nextReviewDate = app(JalaliDateService::class)
                    ->toGregorian($nextReviewDateJalali);
            }

            $lockedLoanRequest->update([
                'status' => LoanRequestStatus::REJECTED,
                'review_note' => $validated['review_note'],
                'next_review_date' => $nextReviewDate,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            $user = $lockedLoanRequest->customer?->user;

            if ($user) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'loan_request_rejected',
                    'title' => 'درخواست وام تأیید نشد',
                    'message' =>
                        'درخواست وام شما پس از بررسی مورد موافقت قرار نگرفت.',
                    'data' => [
                        'loan_request_id' => $lockedLoanRequest->id,
                        'requested_amount' =>
                            $lockedLoanRequest->requested_amount,
                        'review_note' => $validated['review_note'],
                        'next_review_date' => $nextReviewDateJalali,
                    ],
                ]);
            }
        }, 3);

        return redirect()
            ->back()
            ->with('success', 'درخواست وام رد شد.');
    }

    /**
     * تغییر تاریخ مراجعه مجدد درخواست ردشده
     */
    public function updateReviewDate(
        Request $request,
        LoanRequest $loanRequest
    ) {
        $validated = $request->validate([
            'next_review_date' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (! $this->isValidJalaliDate($value)) {
                        $fail('تاریخ مراجعه مجدد باید یک تاریخ شمسی معتبر باشد.');
                    }
                },
            ],
        ]);

        $nextReviewDateJalali =
            $validated['next_review_date'];

        DB::transaction(function () use (
            $loanRequest,
            $nextReviewDateJalali
        ) {
            $lockedLoanRequest = LoanRequest::query()
                ->lockForUpdate()
                ->findOrFail($loanRequest->id);

            if (
                $lockedLoanRequest->status !==
                LoanRequestStatus::REJECTED
            ) {
                abort(
                    422,
                    'فقط درخواست ردشده امکان تغییر تاریخ مراجعه مجدد دارد.'
                );
            }

            $nextReviewDate = app(JalaliDateService::class)
                ->toGregorian($nextReviewDateJalali);

            $lockedLoanRequest->update([
                'next_review_date' => $nextReviewDate,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            $user = $lockedLoanRequest->customer()
                ->with('user')
                ->first()
                ?->user;

            if (! $user) {
                return;
            }

            $notification = Notification::query()
                ->where('user_id', $user->id)
                ->where('type', 'loan_request_rejected')
                ->whereJsonContains(
                    'data->loan_request_id',
                    $lockedLoanRequest->id
                )
                ->latest('id')
                ->first();

            if (! $notification) {
                return;
            }

            $data = $notification->data ?? [];

            $data['loan_request_id'] = $lockedLoanRequest->id;
            $data['requested_amount'] =
                $lockedLoanRequest->requested_amount;
            $data['review_note'] = $lockedLoanRequest->review_note;
            $data['next_review_date'] = $nextReviewDateJalali;

            $notification->update([
                'data' => $data,
                'read_at' => null,
            ]);
        }, 3);

        return redirect()
            ->back()
            ->with('success', 'تاریخ مراجعه مجدد با موفقیت تغییر کرد.');
    }

    /**
     * حذف درخواست
     */
    public function destroy(LoanRequest $loanRequest)
    {
        DB::transaction(function () use ($loanRequest) {
            $lockedLoanRequest = LoanRequest::query()
                ->lockForUpdate()
                ->findOrFail($loanRequest->id);

            if ($lockedLoanRequest->loan_id) {
                throw new RuntimeException(
                    'این درخواست دارای وام است و امکان حذف آن وجود ندارد.'
                );
            }

            $lockedLoanRequest->delete();
        }, 3);

        return redirect()
            ->route('loan-requests.index')
            ->with('success', 'درخواست وام حذف شد.');
    }
}
