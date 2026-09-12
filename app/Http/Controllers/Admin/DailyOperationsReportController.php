<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountingStatus;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DonationPayment;
use App\Models\LoanPayment;
use App\Models\LoanRequest;
use App\Models\SavingsTransfer;
use App\Models\User;
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
        | 1. ثبت‌نام مشتری
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
        | 2 و 7. واریز به حساب پس‌انداز خود / دیگران
        |--------------------------------------------------------------------------
        |
        | فقط واریز موفق درگاه.
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
                function (Builder $query) use ($customerId) {

                    /*
                     * مشتری در این بخش یعنی واریزکننده.
                     */
                    $query->whereIn(
                        'sender_user_id',
                        User::query()
                            ->where(
                                'customer_id',
                                $customerId
                            )
                            ->select('id')
                    );
                }
            )
            ->latest('paid_at')
            ->latest('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | فقط عملیات حسابداری تأیید شده
        |--------------------------------------------------------------------------
        */

        if ($onlyConfirmed) {

            $savingsTransfers = $savingsTransfers->filter(
                fn ($transfer) =>
                    $transfer->accounting_status
                    === AccountingStatus::CONFIRMED
            );
        }


        /*
        |--------------------------------------------------------------------------
        | تفکیک واریز به حساب خود / دیگران
        |--------------------------------------------------------------------------
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
        | 3 و 4. پرداخت قسط خود / دیگران
        |--------------------------------------------------------------------------
        |
        | user_id = پرداخت‌کننده
        |
        | loan.customer = صاحب وام
        |
        */

        $loanPayments = LoanPayment::query()
            ->with([
                'loan.customer.accounts',
                'loan.customer.user',
                'installment',
                'user.customer',
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
                function (Builder $query) use ($customerId) {

                    /*
                     * مشتری در این قسمت یعنی پرداخت‌کننده.
                     */
                    $query->whereIn(
                        'user_id',
                        User::query()
                            ->where(
                                'customer_id',
                                $customerId
                            )
                            ->select('id')
                    );
                }
            )
            ->latest('paid_at')
            ->latest('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | فقط عملیات حسابداری تأیید شده
        |--------------------------------------------------------------------------
        */

        if ($onlyConfirmed) {

            $loanPayments = $loanPayments->filter(
                fn ($payment) =>
                    $payment->accounting_status
                    === AccountingStatus::CONFIRMED
            );
        }


        /*
        |--------------------------------------------------------------------------
        | تفکیک پرداخت قسط خود / دیگران
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
        | 5. کمک / صدقه
        |--------------------------------------------------------------------------
        |
        | منبع واقعی:
        | donation_payments
        |
        | در پروژه فعلی:
        |
        | status = 1  => پرداخت موفق
        | paid_at      => ممکن است null باشد
        |
        | بنابراین موفق بودن پرداخت را با status بررسی می‌کنیم
        | و تاریخ عملیات را از created_at می‌گیریم.
        |
        */

        $donations = DonationPayment::query()
            ->with([
                'customer',
                'account',
            ])
            ->where(
                'status',
                1
            )
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
            ->latest('created_at')
            ->latest('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | 6. درخواست وام
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
            ->latest('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | فیلتر نوع عملیات
        |--------------------------------------------------------------------------
        */

        switch ($operationType) {

            case 'registration':

                $ownSavingsTransfers = collect();
                $otherSavingsTransfers = collect();
                $ownLoanPayments = collect();
                $otherLoanPayments = collect();
                $donations = collect();
                $loanRequests = collect();

                break;


            case 'saving_own':

                $customers = collect();
                $otherSavingsTransfers = collect();
                $ownLoanPayments = collect();
                $otherLoanPayments = collect();
                $donations = collect();
                $loanRequests = collect();

                break;


            case 'saving_other':

                $customers = collect();
                $ownSavingsTransfers = collect();
                $ownLoanPayments = collect();
                $otherLoanPayments = collect();
                $donations = collect();
                $loanRequests = collect();

                break;


            case 'loan_payment_own':

                $customers = collect();
                $ownSavingsTransfers = collect();
                $otherSavingsTransfers = collect();
                $otherLoanPayments = collect();
                $donations = collect();
                $loanRequests = collect();

                break;


            case 'loan_payment_other':

                $customers = collect();
                $ownSavingsTransfers = collect();
                $otherSavingsTransfers = collect();
                $ownLoanPayments = collect();
                $donations = collect();
                $loanRequests = collect();

                break;


            case 'donation':

                $customers = collect();
                $ownSavingsTransfers = collect();
                $otherSavingsTransfers = collect();
                $ownLoanPayments = collect();
                $otherLoanPayments = collect();
                $loanRequests = collect();

                break;


            case 'loan_request':

                $customers = collect();
                $ownSavingsTransfers = collect();
                $otherSavingsTransfers = collect();
                $ownLoanPayments = collect();
                $otherLoanPayments = collect();
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

            'own_savings_transfers' =>
                $ownSavingsTransfers->sum('amount'),

            'other_savings_transfers' =>
                $otherSavingsTransfers->sum('amount'),

            'own_loan_payments' =>
                $ownLoanPayments->sum('amount'),

            'other_loan_payments' =>
                $otherLoanPayments->sum('amount'),

            'donations' =>
                $donations->sum('amount'),

        ];


        /*
        |--------------------------------------------------------------------------
        | مجموع عملیات مالی مشتری
        |--------------------------------------------------------------------------
        |
        | این عدد خالص حسابداری نیست.
        |
        */

        $totalReceived =
            $totals['own_savings_transfers']
            + $totals['other_savings_transfers']
            + $totals['own_loan_payments']
            + $totals['other_loan_payments']
            + $totals['donations'];


        /*
        |--------------------------------------------------------------------------
        | تعداد عملیات
        |--------------------------------------------------------------------------
        */

        $counts = [

            'customers' =>
                $customers->count(),

            'own_savings_transfers' =>
                $ownSavingsTransfers->count(),

            'other_savings_transfers' =>
                $otherSavingsTransfers->count(),

            'own_loan_payments' =>
                $ownLoanPayments->count(),

            'other_loan_payments' =>
                $otherLoanPayments->count(),

            'donations' =>
                $donations->count(),

            'loan_requests' =>
                $loanRequests->count(),

        ];


        /*
        |--------------------------------------------------------------------------
        | مشتریان برای فیلتر
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
        | ارسال به View
        |--------------------------------------------------------------------------
        */

        return view(
            'admin.reports.daily-operations',
            compact(
                'customers',
                'ownSavingsTransfers',
                'otherSavingsTransfers',
                'ownLoanPayments',
                'otherLoanPayments',
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
                'totalReceived',
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
