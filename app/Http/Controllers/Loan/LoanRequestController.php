<?php

namespace App\Http\Controllers\Loan;

use App\Enums\LoanRequestStatus;
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

class LoanRequestController extends Controller
{
    public function __construct(
        private readonly LoanTypeService $loanTypeService
    ) {
    }

    /**
     * لیست درخواست‌ها
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $status = $request->input('status');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $query = LoanRequest::query()
            ->with([
                'customer',
                'loan',
            ]);

        /*
        |--------------------------------------------------------------------------
        | جستجو
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | فیلتر وضعیت
        |--------------------------------------------------------------------------
        */

        if (
            $status &&
            in_array(
                $status,
                array_column(
                    LoanRequestStatus::cases(),
                    'value'
                ),
                true
            )
        ) {
            $query->where('status', $status);
        }

        /*
        |--------------------------------------------------------------------------
        | فیلتر تاریخ
        |--------------------------------------------------------------------------
        */

        if ($fromDate) {
            $fromDateGregorian = app(JalaliDateService::class)
                ->toGregorian($fromDate);

            $query->whereDate(
                'created_at',
                '>=',
                $fromDateGregorian
            );
        }

        if ($toDate) {
            $toDateGregorian = app(JalaliDateService::class)
                ->toGregorian($toDate);

            $query->whereDate(
                'created_at',
                '<=',
                $toDateGregorian
            );
        }

        $loanRequests = $query
            ->latest()
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
     * ثبت درخواست توسط مدیر
     */
    public function store(
        StoreLoanRequestRequest $request
    ) {
        LoanRequest::create([
            'customer_id' =>
                $request->validated('customer_id'),

            'requested_amount' =>
                $request->validated('requested_amount'),

            'description' =>
                $request->validated('description'),

            'status' =>
                LoanRequestStatus::PENDING,
        ]);

        return redirect()
            ->route('loan-requests.index')
            ->with(
                'success',
                'درخواست وام ثبت شد.'
            );
    }

    /**
     * نمایش درخواست
     */
    public function show(
        LoanRequest $loanRequest
    ) {
        $loanRequest->load([
            'customer',
            'customer.user',
            'loanType',
            'loan',
        ]);

        $loanTypes = $this->loanTypeService->getActive();

        return view(
            'loan_requests.show',
            compact(
                'loanRequest',
                'loanTypes'
            )
        );
    }

    /**
     * فرم ویرایش درخواست
     */
    public function edit(
        LoanRequest $loanRequest
    ) {
        $loanRequest->load([
            'customer',
            'customer.user',
            'loanType',
            'loan',
        ]);

        $loanTypes = $this->loanTypeService->getActive();

        return view(
            'loan_requests.edit',
            compact(
                'loanRequest',
                'loanTypes'
            )
        );
    }

    /**
     * بروزرسانی اطلاعات درخواست
     *
     * نکته مهم:
     * تغییر وضعیت از این مسیر ممنوع است.
     *
     * وضعیت فقط از مسیرهای:
     * approve()
     * reject()
     *
     * تغییر می‌کند.
     */
    public function update(
        Request $request,
        LoanRequest $loanRequest
    ) {
        /*
        |--------------------------------------------------------------------------
        | اگر درخواست قبلاً به وام متصل شده، اطلاعات تأییدشده
        | دیگر نباید تغییر کند.
        |--------------------------------------------------------------------------
        */

        if ($loanRequest->loan_id) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'این درخواست به وام متصل شده و امکان ویرایش آن وجود ندارد.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $request->merge([
            'requested_amount' => clean_money(
                $request->input('requested_amount')
            ),

            'approved_amount' => clean_money(
                $request->input('approved_amount')
            ),
        ]);

        $validated = $request->validate([

            'requested_amount' => [
                'required',
                'integer',
                'min:1',
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'approved_amount' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'loan_type_id' => [
                'nullable',
                'exists:loan_types,id',
            ],

            'approved_installment_count' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'approved_installment_interval' => [
                'nullable',
                'integer',
                'in:1,2,3',
            ],

            'review_note' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'next_review_date' => [
                'nullable',
                'string',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | قفل درخواست و بررسی مجدد
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $loanRequest,
            $validated
        ) {

            $lockedLoanRequest = LoanRequest::query()
                ->lockForUpdate()
                ->findOrFail($loanRequest->id);

            /*
            |--------------------------------------------------------------------------
            | جلوگیری از تغییر همزمان بعد از ایجاد وام
            |--------------------------------------------------------------------------
            */

            if ($lockedLoanRequest->loan_id) {
                throw new \RuntimeException(
                    'این درخواست به وام متصل شده و امکان ویرایش آن وجود ندارد.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | تبدیل تاریخ شمسی
            |--------------------------------------------------------------------------
            */

            $nextReviewDate = null;

            if (!empty($validated['next_review_date'])) {
                $nextReviewDate = app(JalaliDateService::class)
                    ->toGregorian(
                        $validated['next_review_date']
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | وضعیت فعلی حفظ می‌شود.
            | این متد اجازه تغییر وضعیت ندارد.
            |--------------------------------------------------------------------------
            */

            if (
                $lockedLoanRequest->status ===
                LoanRequestStatus::APPROVED
            ) {
                $nextReviewDate = null;
            }

            if (
                $lockedLoanRequest->status ===
                LoanRequestStatus::PENDING
            ) {
                $nextReviewDate = null;
            }

            /*
            |--------------------------------------------------------------------------
            | بروزرسانی
            |--------------------------------------------------------------------------
            */

            $lockedLoanRequest->update([

                'requested_amount' =>
                    $validated['requested_amount'],

                'description' =>
                    $validated['description'] ?? null,

                'approved_amount' =>
                    $validated['approved_amount'] ?? null,

                'loan_type_id' =>
                    $validated['loan_type_id'] ?? null,

                'approved_installment_count' =>
                    $validated['approved_installment_count'] ?? null,

                'approved_installment_interval' =>
                    $validated['approved_installment_interval'] ?? null,

                'review_note' =>
                    $validated['review_note'] ?? null,

                'next_review_date' =>
                    $nextReviewDate,

                'reviewed_by' =>
                    auth()->id(),

                'reviewed_at' =>
                    now(),
            ]);
        });

        return redirect()
            ->route(
                'loan-requests.show',
                $loanRequest
            )
            ->with(
                'success',
                'اطلاعات درخواست وام با موفقیت ویرایش شد.'
            );
    }

    /**
     * تایید درخواست
     */
    public function approve(
        ApproveLoanRequestRequest $request,
        LoanRequest $loanRequest
    ) {
        $notificationData = null;
        $userId = null;

        DB::transaction(function () use (
            $request,
            $loanRequest,
            &$notificationData,
            &$userId
        ) {
            $loanRequest = LoanRequest::query()
                ->lockForUpdate()
                ->with('customer.user')
                ->findOrFail($loanRequest->id);

            if (
                $loanRequest->status !==
                LoanRequestStatus::PENDING
            ) {
                abort(
                    422,
                    'این درخواست قبلاً بررسی شده است.'
                );
            }

            if ($loanRequest->loan_id) {
                abort(
                    422,
                    'برای این درخواست قبلاً وام ایجاد شده است.'
                );
            }

            $user = $loanRequest->customer?->user;

            $approvedAmount =
                $request->validated('approved_amount');

            $loanTypeId =
                $request->validated('loan_type_id');

            $approvedInstallmentCount =
                $request->validated(
                    'approved_installment_count'
                );

            $approvedInstallmentInterval =
                $request->validated(
                    'approved_installment_interval'
                );

            $reviewNote =
                $request->validated('review_note');

            $loanRequest->update([

                'status' =>
                    LoanRequestStatus::APPROVED,

                'approved_amount' =>
                    $approvedAmount,

                'loan_type_id' =>
                    $loanTypeId,

                'approved_installment_count' =>
                    $approvedInstallmentCount,

                'approved_installment_interval' =>
                    $approvedInstallmentInterval,

                'review_note' =>
                    $reviewNote,

                'reviewed_by' =>
                    auth()->id(),

                'reviewed_at' =>
                    now(),

                'next_review_date' =>
                    null,
            ]);

            if ($user) {
                $userId = $user->id;

                $notificationData = [
                    'type' =>
                        'loan_request_approved',

                    'title' =>
                        'درخواست وام تأیید شد',

                    'message' =>
                        'درخواست وام شما با مبلغ ' .
                        fa_money($approvedAmount) .
                        ' تأیید شد.',

                    'data' => [
                        'loan_request_id' =>
                            $loanRequest->id,

                        'approved_amount' =>
                            $approvedAmount,

                        'approved_installment_count' =>
                            $approvedInstallmentCount,

                        'approved_installment_interval' =>
                            $approvedInstallmentInterval,

                        'review_note' =>
                            $reviewNote,
                    ],
                ];
            }
        });

        if ($userId && $notificationData) {
            Notification::create([
                'user_id' =>
                    $userId,

                'type' =>
                    $notificationData['type'],

                'title' =>
                    $notificationData['title'],

                'message' =>
                    $notificationData['message'],

                'data' =>
                    $notificationData['data'],
            ]);
        }

        return redirect()
            ->back()
            ->with(
                'success',
                'درخواست وام تایید شد.'
            );
    }

    /**
     * رد درخواست
     */
    public function reject(
        RejectLoanRequestRequest $request,
        LoanRequest $loanRequest
    ) {
        $notificationData = null;
        $userId = null;

        DB::transaction(function () use (
            $request,
            $loanRequest,
            &$notificationData,
            &$userId
        ) {
            $loanRequest = LoanRequest::query()
                ->lockForUpdate()
                ->with('customer.user')
                ->findOrFail($loanRequest->id);

            if (
                $loanRequest->status !==
                LoanRequestStatus::PENDING
            ) {
                abort(
                    422,
                    'این درخواست قبلاً بررسی شده است.'
                );
            }

            if ($loanRequest->loan_id) {
                abort(
                    422,
                    'برای این درخواست قبلاً وام ایجاد شده است.'
                );
            }

            $user = $loanRequest->customer?->user;

            $requestedAmount =
                $loanRequest->requested_amount;

            $reviewNote =
                $request->validated('review_note');

            $nextReviewDate = null;
            $nextReviewDateJalali = null;

            if ($request->filled('next_review_date')) {
                $nextReviewDateJalali =
                    $request->validated(
                        'next_review_date'
                    );

                $nextReviewDate =
                    app(JalaliDateService::class)
                        ->toGregorian(
                            $nextReviewDateJalali
                        );
            }

            $loanRequest->update([

                'status' =>
                    LoanRequestStatus::REJECTED,

                'review_note' =>
                    $reviewNote,

                'next_review_date' =>
                    $nextReviewDate,

                'reviewed_by' =>
                    auth()->id(),

                'reviewed_at' =>
                    now(),
            ]);

            if ($user) {
                $userId = $user->id;

                $notificationData = [

                    'type' =>
                        'loan_request_rejected',

                    'title' =>
                        'درخواست وام تأیید نشد',

                    'message' =>
                        'درخواست وام شما پس از بررسی مورد موافقت قرار نگرفت.',

                    'data' => [

                        'loan_request_id' =>
                            $loanRequest->id,

                        'requested_amount' =>
                            $requestedAmount,

                        'review_note' =>
                            $reviewNote,

                        'next_review_date' =>
                            $nextReviewDateJalali,
                    ],
                ];
            }
        });

        if ($userId && $notificationData) {
            Notification::create([
                'user_id' =>
                    $userId,

                'type' =>
                    $notificationData['type'],

                'title' =>
                    $notificationData['title'],

                'message' =>
                    $notificationData['message'],

                'data' =>
                    $notificationData['data'],
            ]);
        }

        return redirect()
            ->back()
            ->with(
                'success',
                'درخواست وام رد شد.'
            );
    }

    /**
     * تغییر تاریخ مراجعه مجدد
     */
    public function updateReviewDate(
        Request $request,
        LoanRequest $loanRequest
    ) {
        if (
            $loanRequest->status !==
            LoanRequestStatus::REJECTED
        ) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'فقط درخواست رد شده امکان تغییر تاریخ مراجعه مجدد دارد.'
                );
        }

        $validated = $request->validate([

            'next_review_date' => [
                'required',
                'string',
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
                    'فقط درخواست رد شده امکان تغییر تاریخ مراجعه مجدد دارد.'
                );
            }

            $nextReviewDate =
                app(JalaliDateService::class)
                    ->toGregorian(
                        $nextReviewDateJalali
                    );

            $lockedLoanRequest->update([

                'next_review_date' =>
                    $nextReviewDate,

                'reviewed_by' =>
                    auth()->id(),

                'reviewed_at' =>
                    now(),

            ]);

            $lockedLoanRequest->load([
                'customer.user',
            ]);

            $user =
                $lockedLoanRequest->customer?->user;

            if (!$user) {
                return;
            }

            $notification =
                Notification::query()
                    ->where(
                        'user_id',
                        $user->id
                    )
                    ->where(
                        'type',
                        'loan_request_rejected'
                    )
                    ->whereJsonContains(
                        'data->loan_request_id',
                        $lockedLoanRequest->id
                    )
                    ->latest('id')
                    ->first();

            if (!$notification) {
                return;
            }

            $data =
                $notification->data ?? [];

            $data['loan_request_id'] =
                $lockedLoanRequest->id;

            $data['requested_amount'] =
                $lockedLoanRequest->requested_amount;

            $data['review_note'] =
                $lockedLoanRequest->review_note;

            $data['next_review_date'] =
                $nextReviewDateJalali;

            $notification->update([

                'data' =>
                    $data,

                'read_at' =>
                    null,

            ]);
        });

        return redirect()
            ->back()
            ->with(
                'success',
                'تاریخ مراجعه مجدد با موفقیت تغییر کرد.'
            );
    }

    /**
     * حذف درخواست
     */
    public function destroy(
        LoanRequest $loanRequest
    ) {
        if ($loanRequest->loan_id) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'این درخواست دارای وام است و امکان حذف آن وجود ندارد.'
                );
        }

        $loanRequest->delete();

        return redirect()
            ->route(
                'loan-requests.index'
            )
            ->with(
                'success',
                'درخواست وام حذف شد.'
            );
    }
}
