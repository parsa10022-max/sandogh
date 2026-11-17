<?php

namespace App\Http\Controllers\Customer;

use App\Enums\LoanRequestStatus;
use App\Enums\LoanStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoanRequest\StoreCustomerLoanRequestRequest;
use App\Models\Customer;
use App\Models\LoanRequest;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LoanRequestController extends Controller
{
    /**
     * دریافت مشتری متصل به کاربر
     */
    private function getAuthenticatedCustomer(): Customer
    {
        $customer = auth()->user()?->customer;

        abort_unless($customer, 403);

        return $customer;
    }

    /**
     * فرم ثبت درخواست وام
     */
    public function create(): View|RedirectResponse
    {
        $customer = $this->getAuthenticatedCustomer();

        $restriction = $this->getLoanRequestRestriction($customer);

        if ($restriction) {
            return redirect()
                ->route('customer.loan-requests.index')
                ->with('error', $restriction);
        }

        return view('customer.loan_requests.create');
    }

    /**
     * ثبت درخواست وام
     */
    public function store(
        StoreCustomerLoanRequestRequest $request
    ): RedirectResponse {
        $customer = $this->getAuthenticatedCustomer();

        /*
        |--------------------------------------------------------------------------
        | قفل مشتری و ثبت اتمیک درخواست
        |--------------------------------------------------------------------------
        |
        | درخواست‌های هم‌زمان برای یک مشتری پشت سر هم اجرا می‌شوند.
        | بررسی محدودیت و ثبت درخواست داخل همان تراکنش انجام می‌شود.
        |
        */

        $result = DB::transaction(function () use ($request, $customer) {

            $lockedCustomer = Customer::query()
                ->whereKey($customer->id)
                ->lockForUpdate()
                ->first();

            abort_unless($lockedCustomer, 403);

            /*
            |--------------------------------------------------------------------------
            | بررسی مجدد محدودیت پس از گرفتن قفل
            |--------------------------------------------------------------------------
            */

            $restriction = $this->getLoanRequestRestriction(
                $lockedCustomer
            );

            if ($restriction) {
                return [
                    'restriction' => $restriction,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | ثبت درخواست
            |--------------------------------------------------------------------------
            */

            $loanRequest = LoanRequest::create([
                'customer_id' => $lockedCustomer->id,
                'requested_amount' => $request->validated('requested_amount'),
                'description' => $request->validated('description'),
                'status' => LoanRequestStatus::PENDING,
            ]);

            /*
            |--------------------------------------------------------------------------
            | اعلان درخواست وام برای کاربران مدیریتی
            |--------------------------------------------------------------------------
            */

            $managerUsers = User::query()
                ->whereIn('role', [
                    UserRole::ADMIN->value,
                    UserRole::CEO->value,
                    UserRole::BOARD_MEMBER->value,
                    UserRole::OPERATOR->value,
                ])
                ->get();

            foreach ($managerUsers as $managerUser) {
                Notification::create([
                    'user_id' => $managerUser->id,
                    'type' => 'loan_request_created',
                    'title' => 'درخواست وام جدید',
                    'message' =>
                        'عضو «' .
                        $lockedCustomer->full_name .
                        '» درخواست وامی به مبلغ ' .
                        fa_money($loanRequest->requested_amount) .
                        ' ثبت کرده است.',
                    'data' => [
                        'loan_request_id' => $loanRequest->id,
                        'customer_id' => $lockedCustomer->id,
                        'customer_name' => $lockedCustomer->full_name,
                        'requested_amount' => $loanRequest->requested_amount,
                    ],
                    'read_at' => null,
                ]);
            }

            return [
                'restriction' => null,
            ];
        }, 3);

        if ($result['restriction']) {
            return back()
                ->withInput()
                ->with('error', $result['restriction']);
        }

        return redirect()
            ->route('customer.loan-requests.index')
            ->with(
                'success',
                'درخواست وام شما با موفقیت ثبت شد و در حال بررسی است.'
            );
    }

    /**
     * لیست درخواست‌های وام مشتری
     */
    public function index(): View
    {
        $customer = $this->getAuthenticatedCustomer();

        $latestRequest = LoanRequest::query()
            ->where('customer_id', $customer->id)
            ->with([
                'loanType',
                'loan',
            ])
            ->latest('id')
            ->first();

        $hasActiveLoan = $customer->loans()
            ->active()
            ->exists();

        $hasPendingRequest = LoanRequest::query()
            ->where('customer_id', $customer->id)
            ->where(
                'status',
                LoanRequestStatus::PENDING->value
            )
            ->exists();

        $latestRejectedRequest = LoanRequest::query()
            ->where('customer_id', $customer->id)
            ->where(
                'status',
                LoanRequestStatus::REJECTED->value
            )
            ->latest('id')
            ->first();

        $restriction = $this->getLoanRequestRestriction($customer);

        $canCreateNewRequest = $restriction === null;

        $loanRequests = LoanRequest::query()
            ->where('customer_id', $customer->id)
            ->with([
                'loanType',
                'loan',
            ])
            ->latest('id')
            ->paginate(10);

        return view(
            'customer.loan_requests.index',
            compact(
                'loanRequests',
                'hasActiveLoan',
                'hasPendingRequest',
                'latestRejectedRequest',
                'latestRequest',
                'canCreateNewRequest'
            )
        );
    }

    /**
     * نمایش جزئیات درخواست
     */
    public function show(
        LoanRequest $loanRequest
    ): View {
        $customer = $this->getAuthenticatedCustomer();

        abort_unless(
            (int) $loanRequest->customer_id === (int) $customer->id,
            403
        );

        $loanRequest->load([
            'loanType',
            'loan',
        ]);

        return view(
            'customer.loan_requests.show',
            compact('loanRequest')
        );
    }

    /**
     * بررسی امکان ثبت درخواست وام جدید
     *
     * null: ثبت درخواست مجاز است.
     * string: ثبت درخواست ممنوع است و پیام محدودیت برمی‌گردد.
     */
    private function getLoanRequestRestriction(
        Customer $customer
    ): ?string {
        /*
        |--------------------------------------------------------------------------
        | 1. وام فعال
        |--------------------------------------------------------------------------
        */

        if (
            $customer->loans()
                ->active()
                ->exists()
        ) {
            return 'شما در حال حاضر یک وام فعال دارید و امکان ثبت درخواست وام جدید وجود ندارد.';
        }

        /*
        |--------------------------------------------------------------------------
        | 2. آخرین درخواست مشتری
        |--------------------------------------------------------------------------
        */

        $latestRequest = LoanRequest::query()
            ->where('customer_id', $customer->id)
            ->with('loan')
            ->latest('id')
            ->first();

        if (!$latestRequest) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | 3. درخواست در حال بررسی
        |--------------------------------------------------------------------------
        */

        if (
            $latestRequest->status ===
            LoanRequestStatus::PENDING
        ) {
            return 'شما یک درخواست وام در حال بررسی دارید و امکان ثبت درخواست جدید وجود ندارد.';
        }

        /*
        |--------------------------------------------------------------------------
        | 4. درخواست تأیید شده
        |--------------------------------------------------------------------------
        */

        if (
            $latestRequest->status ===
            LoanRequestStatus::APPROVED
        ) {
            if (!$latestRequest->loan_id) {
                return 'درخواست وام شما تأیید شده است و هنوز فرآیند ایجاد وام آن تکمیل نشده است.';
            }

            $loan = $latestRequest->loan;

            if (!$loan) {
                return 'درخواست وام شما تأیید شده است و هنوز فرآیند ایجاد وام آن تکمیل نشده است.';
            }

            if (
                $loan->status === LoanStatus::ACTIVE
            ) {
                return 'درخواست وام شما تأیید شده و وام شما فعال است؛ تا زمان تسویه وام امکان ثبت درخواست جدید وجود ندارد.';
            }

            if (
                $loan->status === LoanStatus::FINISHED
            ) {
                return null;
            }

            return 'درخواست وام قبلی شما هنوز در وضعیت نهایی قابل ثبت درخواست جدید نیست.';
        }

        /*
        |--------------------------------------------------------------------------
        | 5. درخواست رد شده
        |--------------------------------------------------------------------------
        */

        if (
            $latestRequest->status ===
            LoanRequestStatus::REJECTED
        ) {
            if (!$latestRequest->next_review_date) {
                return 'درخواست وام قبلی شما رد شده است و هنوز تاریخ مجاز برای درخواست مجدد تعیین نشده است.';
            }

            $nextReviewDate = Carbon::parse(
                $latestRequest->next_review_date
            )->startOfDay();

            $today = now()->startOfDay();

            if ($today->lt($nextReviewDate)) {
                return 'امکان ثبت درخواست جدید تا تاریخ ' .
                    jdate($nextReviewDate)->format('Y/m/d') .
                    ' وجود ندارد.';
            }

            return null;
        }
        /*
        |--------------------------------------------------------------------------
        | 6. درخواست لغو شده
        |--------------------------------------------------------------------------
        */

        if (
            $latestRequest->status ===
            LoanRequestStatus::CANCELLED
        ) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | 6. وضعیت ناشناخته
        |--------------------------------------------------------------------------
        */

        return 'وضعیت درخواست وام قبلی شما اجازه ثبت درخواست جدید را نمی‌دهد.';
    }
}
