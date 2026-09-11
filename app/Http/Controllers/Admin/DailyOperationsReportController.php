<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountingStatus;
use App\Enums\DonationStatus;
use App\Enums\TransactionType;
use App\Enums\WithdrawalStatus;
use App\Http\Controllers\Controller;
use App\Models\AccountTransaction;
use App\Models\Customer;
use App\Models\Donation;
use App\Models\LoanPayment;
use App\Models\LoanRequest;
use App\Models\SavingsTransfer;
use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DailyOperationsReportController extends Controller
{
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | فیلترها
        |--------------------------------------------------------------------------
        */

        $fromDate = $this->convertJalaliDate(
            $request->input('from_date')
        );

        $toDate = $this->convertJalaliDate(
            $request->input('to_date')
        );

        $operationType = $request->input(
            'operation_type',
            'all'
        );

        $customerId = $request->input('customer_id');

        $status = $request->input(
            'status',
            'all'
        );

        $onlyConfirmed = $request->boolean(
            'only_confirmed'
        );


        /*
        |--------------------------------------------------------------------------
        | ثبت‌نام اعضای جدید
        |--------------------------------------------------------------------------
        */

        $customers = Customer::query()
            ->with('user')
            ->when(
                $fromDate,
                fn (Builder $query) =>
                $query->whereDate(
                    'created_at',
                    '>=',
                    $fromDate
                )
            )
            ->when(
                $toDate,
                fn (Builder $query) =>
                $query->whereDate(
                    'created_at',
                    '<=',
                    $toDate
                )
            )
            ->when(
                $customerId,
                fn (Builder $query) =>
                $query->where(
                    'id',
                    $customerId
                )
            )
            ->latest('created_at')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | واریزهای حساب
        |--------------------------------------------------------------------------
        |
        | این بخش مربوط به AccountTransaction است.
        | انتقال پس‌انداز در این بخش قرار نمی‌گیرد.
        |
        */

        $deposits = AccountTransaction::query()
            ->with([
                'account.customer',
                'creator',
            ])
            ->where(
                'transaction_type',
                TransactionType::DEPOSIT
            )
            ->where(function (Builder $query) {

                $query
                    ->whereNull('description')
                    ->orWhere(
                        'description',
                        'not like',
                        'برگشت مبلغ برداشت%'
                    );

            })
            ->when(
                $fromDate,
                fn (Builder $query) =>
                $query->whereDate(
                    'transaction_date',
                    '>=',
                    $fromDate
                )
            )
            ->when(
                $toDate,
                fn (Builder $query) =>
                $query->whereDate(
                    'transaction_date',
                    '<=',
                    $toDate
                )
            )
            ->when(
                $customerId,
                fn (Builder $query) =>
                $query->whereHas(
                    'account',
                    fn (Builder $accountQuery) =>
                    $accountQuery->where(
                        'customer_id',
                        $customerId
                    )
                )
            )
            ->latest('transaction_date')
            ->latest('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | برداشت‌ها
        |--------------------------------------------------------------------------
        */

        $withdrawals = Withdrawal::query()
            ->with([
                'account.customer',
                'paidBy',
            ])
            ->where(
                'status',
                WithdrawalStatus::PAID
            )
            ->when(
                $fromDate,
                fn (Builder $query) =>
                $query->whereDate(
                    'paid_at',
                    '>=',
                    $fromDate
                )
            )
            ->when(
                $toDate,
                fn (Builder $query) =>
                $query->whereDate(
                    'paid_at',
                    '<=',
                    $toDate
                )
            )
            ->when(
                $customerId,
                fn (Builder $query) =>
                $query->whereHas(
                    'account',
                    fn (Builder $accountQuery) =>
                    $accountQuery->where(
                        'customer_id',
                        $customerId
                    )
                )
            )
            ->latest('paid_at')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | پرداخت اقساط
        |--------------------------------------------------------------------------
        */

        $loanPayments = LoanPayment::query()
            ->with([
                'loan.customer.accounts',
                'loan.customer.user',
                'installment',
                'user',
            ])
            ->whereNotNull('paid_at')
            ->when(
                $fromDate,
                fn (Builder $query) =>
                $query->whereDate(
                    'paid_at',
                    '>=',
                    $fromDate
                )
            )
            ->when(
                $toDate,
                fn (Builder $query) =>
                $query->whereDate(
                    'paid_at',
                    '<=',
                    $toDate
                )
            )
            ->when(
                $customerId,
                fn (Builder $query) =>
                $query->whereHas(
                    'loan',
                    fn (Builder $loanQuery) =>
                    $loanQuery->where(
                        'customer_id',
                        $customerId
                    )
                )
            )
            ->latest('paid_at')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | انتقال‌های پس‌انداز
        |--------------------------------------------------------------------------
        |
        | فقط انتقال‌های موفق.
        |
        */

        $savingsTransfers = SavingsTransfer::query()
            ->with([
                'sender',
                'receiver.user',
                'receiver.accounts',
                'account.customer',
            ])
            ->where(
                'status',
                'paid'
            )
            ->when(
                $fromDate,
                fn (Builder $query) =>
                $query->whereDate(
                    'paid_at',
                    '>=',
                    $fromDate
                )
            )
            ->when(
                $toDate,
                fn (Builder $query) =>
                $query->whereDate(
                    'paid_at',
                    '<=',
                    $toDate
                )
            )
            ->when(
                $customerId,
                function (Builder $query) use ($customerId) {

                    $query->where(function (Builder $q) use ($customerId) {

                        /*
                         * مشتری مقصد
                         */
                        $q->where(
                            'receiver_customer_id',
                            $customerId
                        );

                        /*
                         * مشتری صاحب حساب مبدأ
                         */
                        $q->orWhereHas(
                            'account',
                            fn (Builder $accountQuery) =>
                            $accountQuery->where(
                                'customer_id',
                                $customerId
                            )
                        );

                    });

                }
            )
            ->latest('paid_at')
            ->latest('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | کمک‌های صندوق
        |--------------------------------------------------------------------------
        */

        $donations = Donation::query()
            ->with([
                'customer.accounts',
                'donationType',
                'creator',
            ])
            ->where(
                'status',
                DonationStatus::SUCCESS
            )
            ->when(
                $fromDate,
                fn (Builder $query) =>
                $query->whereDate(
                    'paid_at',
                    '>=',
                    $fromDate
                )
            )
            ->when(
                $toDate,
                fn (Builder $query) =>
                $query->whereDate(
                    'paid_at',
                    '<=',
                    $toDate
                )
            )
            ->when(
                $customerId,
                fn (Builder $query) =>
                $query->where(
                    'customer_id',
                    $customerId
                )
            )
            ->latest('paid_at')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | درخواست‌های وام
        |--------------------------------------------------------------------------
        */

        $loanRequests = LoanRequest::query()
            ->with([
                'customer',
                'loanType',
                'reviewer',
            ])
            ->when(
                $fromDate,
                fn (Builder $query) =>
                $query->whereDate(
                    'created_at',
                    '>=',
                    $fromDate
                )
            )
            ->when(
                $toDate,
                fn (Builder $query) =>
                $query->whereDate(
                    'created_at',
                    '<=',
                    $toDate
                )
            )
            ->when(
                $customerId,
                fn (Builder $query) =>
                $query->where(
                    'customer_id',
                    $customerId
                )
            )
            ->when(
                $status !== 'all',
                fn (Builder $query) =>
                $query->where(
                    'status',
                    $status
                )
            )
            ->latest('created_at')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | فقط عملیات مالی قطعی
        |--------------------------------------------------------------------------
        */

        if ($onlyConfirmed) {

            $savingsTransfers = $savingsTransfers->filter(
                fn ($transfer) =>
                    $transfer->accounting_status
                    === AccountingStatus::CONFIRMED
            );

            $loanPayments = $loanPayments->filter(
                fn ($payment) =>
                    $payment->accounting_status
                    === AccountingStatus::CONFIRMED
            );
        }


        /*
        |--------------------------------------------------------------------------
        | تفکیک واریز به حساب پس‌انداز خود و دیگران
        |--------------------------------------------------------------------------
        |
        | خود:
        | صاحب مشتری مقصد = کاربری که انتقال را انجام داده است.
        |
        | دیگران:
        | صاحب مشتری مقصد با کاربر فرستنده متفاوت است.
        |
        */

        $ownSavingsTransfers = $savingsTransfers->filter(
            function ($transfer) {

                $receiverUserId =
                    $transfer->receiver?->user?->id;

                return $receiverUserId !== null
                    && $receiverUserId === $transfer->sender_user_id;
            }
        );


        $otherSavingsTransfers = $savingsTransfers->filter(
            function ($transfer) {

                $receiverUserId =
                    $transfer->receiver?->user?->id;

                return $receiverUserId === null
                    || $receiverUserId !== $transfer->sender_user_id;
            }
        );


        /*
        |--------------------------------------------------------------------------
        | تفکیک پرداخت قسط خود و دیگران
        |--------------------------------------------------------------------------
        */

        $ownLoanPayments = $loanPayments->filter(
            function ($payment) {

                $customerUserId =
                    $payment->loan?->customer?->user?->id;

                return $customerUserId !== null
                    && $customerUserId === $payment->user_id;
            }
        );


        $otherLoanPayments = $loanPayments->filter(
            function ($payment) {

                $customerUserId =
                    $payment->loan?->customer?->user?->id;

                return $customerUserId === null
                    || $customerUserId !== $payment->user_id;
            }
        );


        /*
        |--------------------------------------------------------------------------
        | فیلتر نوع عملیات
        |--------------------------------------------------------------------------
        |
        | نکته:
        | transfer شامل هر دو نوع انتقال پس‌انداز است.
        |
        */

        switch ($operationType) {

            case 'registration':

                $deposits = collect();
                $withdrawals = collect();
                $loanPayments = collect();
                $savingsTransfers = collect();
                $ownSavingsTransfers = collect();
                $otherSavingsTransfers = collect();
                $donations = collect();
                $loanRequests = collect();

                break;


            case 'deposit':

                $customers = collect();
                $withdrawals = collect();
                $loanPayments = collect();
                $savingsTransfers = collect();
                $ownSavingsTransfers = collect();
                $otherSavingsTransfers = collect();
                $donations = collect();
                $loanRequests = collect();

                break;


            case 'withdrawal':

                $customers = collect();
                $deposits = collect();
                $loanPayments = collect();
                $savingsTransfers = collect();
                $ownSavingsTransfers = collect();
                $otherSavingsTransfers = collect();
                $donations = collect();
                $loanRequests = collect();

                break;


            case 'loan_payment':

                $customers = collect();
                $deposits = collect();
                $withdrawals = collect();
                $savingsTransfers = collect();
                $ownSavingsTransfers = collect();
                $otherSavingsTransfers = collect();
                $donations = collect();
                $loanRequests = collect();

                break;


            case 'transfer':

                $customers = collect();
                $deposits = collect();
                $withdrawals = collect();
                $loanPayments = collect();
                $donations = collect();
                $loanRequests = collect();

                break;


            case 'donation':

                $customers = collect();
                $deposits = collect();
                $withdrawals = collect();
                $loanPayments = collect();
                $savingsTransfers = collect();
                $ownSavingsTransfers = collect();
                $otherSavingsTransfers = collect();
                $loanRequests = collect();

                break;


            case 'loan_request':

                $customers = collect();
                $deposits = collect();
                $withdrawals = collect();
                $loanPayments = collect();
                $savingsTransfers = collect();
                $ownSavingsTransfers = collect();
                $otherSavingsTransfers = collect();
                $donations = collect();

                break;


            case 'all':
            default:

                break;
        }


        /*
        |--------------------------------------------------------------------------
        | جمع مبالغ
        |--------------------------------------------------------------------------
        */

        $totals = [

            'deposits' =>
                $deposits->sum('amount'),

            'withdrawals' =>
                $withdrawals->sum('amount'),

            'loan_payments' =>
                $loanPayments->sum('amount'),

            'savings_transfers' =>
                $savingsTransfers->sum('amount'),

            'own_savings_transfers' =>
                $ownSavingsTransfers->sum('amount'),

            'other_savings_transfers' =>
                $otherSavingsTransfers->sum('amount'),

            'donations' =>
                $donations->sum('amount'),

        ];


        /*
        |--------------------------------------------------------------------------
        | خالص عملیات مالی
        |--------------------------------------------------------------------------
        |
        | انتقال بین حساب اعضا در خالص صندوق لحاظ نمی‌شود.
        |
        */

        $netAmount =
            $totals['deposits']
            + $totals['loan_payments']
            + $totals['donations']
            - $totals['withdrawals'];


        /*
        |--------------------------------------------------------------------------
        | تعداد عملیات
        |--------------------------------------------------------------------------
        */

        $counts = [

            'customers' =>
                $customers->count(),

            'deposits' =>
                $deposits->count(),

            'withdrawals' =>
                $withdrawals->count(),

            'loan_payments' =>
                $loanPayments->count(),

            'savings_transfers' =>
                $savingsTransfers->count(),

            'own_savings_transfers' =>
                $ownSavingsTransfers->count(),

            'other_savings_transfers' =>
                $otherSavingsTransfers->count(),

            'donations' =>
                $donations->count(),

            'loan_requests' =>
                $loanRequests->count(),

        ];


        /*
        |--------------------------------------------------------------------------
        | لیست اعضا برای فیلتر
        |--------------------------------------------------------------------------
        */

        $customersForFilter = Customer::query()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get([
                'id',
                'customer_code',
                'first_name',
                'last_name',
            ]);


        /*
        |--------------------------------------------------------------------------
        | ارسال اطلاعات به View
        |--------------------------------------------------------------------------
        */

        return view(
            'admin.reports.daily-operations',
            compact(
                'customers',
                'deposits',
                'withdrawals',
                'loanPayments',
                'ownLoanPayments',
                'otherLoanPayments',
                'savingsTransfers',
                'ownSavingsTransfers',
                'otherSavingsTransfers',
                'donations',
                'loanRequests',
                'totals',
                'counts',
                'customersForFilter',
                'operationType',
                'customerId',
                'status',
                'onlyConfirmed',
                'fromDate',
                'toDate',
                'netAmount',
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | تبدیل تاریخ شمسی به میلادی
    |--------------------------------------------------------------------------
    */

    private function convertJalaliDate(
        ?string $date
    ): ?string {

        if (empty($date)) {
            return null;
        }

        try {

            return \Morilog\Jalali\Jalalian::fromFormat(
                'Y/m/d',
                trim($date)
            )
                ->toCarbon()
                ->format('Y-m-d');

        } catch (\Throwable $e) {

            return null;
        }
    }
}
