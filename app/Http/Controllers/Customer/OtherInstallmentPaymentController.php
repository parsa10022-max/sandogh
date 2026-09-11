<?php

namespace App\Http\Controllers\Customer;

use App\Enums\InstallmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Installment;
use App\Models\Loan;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;

class OtherInstallmentPaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {
    }

    /**
     * نمایش صفحه پرداخت قسط دیگران
     *
     * جستجو و نمایش نتیجه در همان صفحه
     */
    public function create(Request $request)
    {
        $loan = null;
        $installment = null;

        if ($request->filled('loan_number')) {

            /*
            |--------------------------------------------------------------------------
            | دریافت شماره وام
            |--------------------------------------------------------------------------
            */

            $input = trim($request->loan_number);

            /*
            |--------------------------------------------------------------------------
            | تبدیل اعداد فارسی و عربی به انگلیسی
            |--------------------------------------------------------------------------
            */

            $input = strtr($input, [
                '۰' => '0',
                '۱' => '1',
                '۲' => '2',
                '۳' => '3',
                '۴' => '4',
                '۵' => '5',
                '۶' => '6',
                '۷' => '7',
                '۸' => '8',
                '۹' => '9',

                '٠' => '0',
                '١' => '1',
                '٢' => '2',
                '٣' => '3',
                '٤' => '4',
                '٥' => '5',
                '٦' => '6',
                '٧' => '7',
                '٨' => '8',
                '٩' => '9',
            ]);

            /*
            |--------------------------------------------------------------------------
            | حذف فاصله و خط تیره
            |--------------------------------------------------------------------------
            */

            $input = preg_replace('/[\s\-]/', '', $input);

            /*
            |--------------------------------------------------------------------------
            | جستجوی وام
            |
            | مثال:
            |
            | 2911-61110006
            | 291161110006
            | 61110006
            |--------------------------------------------------------------------------
            */

            $loan = Loan::query()
                ->with([
                    'customer',
                    'loanType',
                    'installments',
                ])
                ->join(
                    'loan_types',
                    'loan_types.id',
                    '=',
                    'loans.loan_type_id'
                )
                ->where(function ($query) use ($input) {

                    $query
                        ->whereRaw(
                            "CONCAT(loan_types.prefix, loans.loan_number) = ?",
                            [$input]
                        )
                        ->orWhere(
                            'loans.loan_number',
                            $input
                        );
                })
                ->select('loans.*')
                ->first();

            /*
            |--------------------------------------------------------------------------
            | وام پیدا نشد
            |--------------------------------------------------------------------------
            */

            if (! $loan) {
                return view(
                    'customer.installments.others.create',
                    [
                        'loan' => null,
                        'installment' => null,
                        'searchError' => 'وامی با این شماره پیدا نشد.',
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | پیدا کردن اولین قسط پرداخت‌نشده
            |--------------------------------------------------------------------------
            */

            $installment = $loan->installments
                ->where(
                    'status',
                    InstallmentStatus::PENDING
                )
                ->sortBy('installment_number')
                ->first();

            /*
            |--------------------------------------------------------------------------
            | قسط پرداخت‌نشده وجود ندارد
            |--------------------------------------------------------------------------
            */

            if (! $installment) {
                return view(
                    'customer.installments.others.create',
                    [
                        'loan' => $loan,
                        'installment' => null,
                        'searchError' => 'تمام اقساط این وام پرداخت شده است.',
                    ]
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | نمایش صفحه
        |--------------------------------------------------------------------------
        */

        return view(
            'customer.installments.others.create',
            compact(
                'loan',
                'installment'
            )
        );
    }

    /**
     * شروع پرداخت قسط دیگران
     */
    public function pay(Request $request)
    {
        $request->validate([
            'installment_id' => [
                'required',
                'exists:installments,id',
            ],
        ]);

        $installment = Installment::with('loan')
            ->findOrFail(
                $request->installment_id
            );

        /*
        |--------------------------------------------------------------------------
        | جلوگیری از پرداخت قسط خود مشتری
        |--------------------------------------------------------------------------
        */

        $customer = auth()->user()->customer;

        if (
            $customer &&
            $installment->loan->customer_id === $customer->id
        ) {
            return back()->with(
                'error',
                'برای پرداخت قسط خودتان از بخش پرداخت اقساط خود استفاده کنید.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | شروع پرداخت قسط دیگران
        |--------------------------------------------------------------------------
        */

        $result = $this->paymentService
            ->startPayment(
                $installment,
                true
            );

        if (! ($result['success'] ?? false)) {
            return back()->with(
                'error',
                $result['message']
                ?? 'خطا در شروع پرداخت.'
            );
        }

        return redirect()->away(
            $result['redirect_url']
        );
    }
}
