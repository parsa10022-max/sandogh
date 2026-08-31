<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\Savings\SavingsTransferService;
use Illuminate\Http\Request;


class SavingsTransferController extends Controller
{

    public function __construct(
        private readonly SavingsTransferService $savingsTransferService,
    ) {
    }



    /**
     * فرم واریز
     */
    public function create()
    {
        return view(
            'customer.savings-transfer.create'
        );
    }



    /**
     * جستجوی عضو مقصد
     */
    /**
     * جستجوی حساب پس‌انداز مقصد
     *
     * ورودی‌های قابل قبول:
     *
     * 6111-000011
     * 6111000011
     * 000011
     */
    public function search(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string',
        ]);

        /*
        |--------------------------------------------------------------------------
        | پاکسازی شماره حساب
        |--------------------------------------------------------------------------
        */

        $keyword = trim($request->keyword);

        // اعداد فارسی
        $keyword = strtr($keyword, [
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
        ]);

        // اعداد عربی
        $keyword = strtr($keyword, [
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
        | حذف خط تیره و فاصله
        |--------------------------------------------------------------------------
        */

        $keyword = str_replace(
            ['-', '–', '—', ' ', '٬', ','],
            '',
            $keyword
        );


        /*
        |--------------------------------------------------------------------------
        | اگر فقط 6 رقم آخر وارد شده باشد
        |
        | 000011
        |
        | تبدیل می‌شود به:
        |
        | 6111000011
        |--------------------------------------------------------------------------
        */

        if (
            strlen($keyword) === 6 &&
            ctype_digit($keyword)
        ) {
            $keyword = '6111' . $keyword;
        }


        /*
        |--------------------------------------------------------------------------
        | جستجوی حساب پس‌انداز فعال
        |--------------------------------------------------------------------------
        */

        $account = \App\Models\Account::query()
            ->where(
                'account_type',
                \App\Enums\AccountType::SAVING->value
            )
            ->where(
                'status',
                \App\Enums\AccountStatus::ACTIVE->value
            )
            ->where(
                'account_number',
                $keyword
            )
            ->with('customer')
            ->first();


        /*
        |--------------------------------------------------------------------------
        | حساب پیدا نشد
        |--------------------------------------------------------------------------
        */

        if (! $account) {

            return response()->json([
                'found' => false,

                'message' =>
                    'حساب پس‌انداز فعال با این شماره پیدا نشد.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | مشتری صاحب حساب
        |--------------------------------------------------------------------------
        */

        $customer = $account->customer;


        /*
        |--------------------------------------------------------------------------
        | نتیجه
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'found' => true,

            'customer' => [

                'id' =>
                    $customer->id,

                'name' =>
                    $customer->full_name,

                'account_number' =>
                    $account->account_number,

            ],

        ]);
    }



    /**
     * شروع پرداخت
     */
    public function store(Request $request)
    {

        $request->validate([

            'receiver_customer_id' =>
                'required|exists:customers,id',

            'amount' =>
                'required|integer|min:1000',

        ]);



        $receiver = Customer::findOrFail(
            $request->receiver_customer_id
        );



        $result =
            $this->savingsTransferService->startPayment(

                $receiver,

                $request->amount

            );



        return redirect(
            $result['gateway']['redirect_url']
        );

    }

    public function ownDepositCreate()
    {
        $customer = auth()->user()->customer;

        $account = $customer->accounts()
            ->where(
                'account_type',
                \App\Enums\AccountType::SAVING->value
            )
            ->where(
                'status',
                \App\Enums\AccountStatus::ACTIVE->value
            )
            ->firstOrFail();


        return view(
            'customer.savings.deposit.create',
            compact('account')
        );
    }

    public function ownDepositStore(Request $request)
    {
        $request->validate([

            'amount' => [
                'required',
                'integer',
                'min:50000'
            ],

        ]);


        $customer = auth()->user()->customer;


        $response = $this->savingsTransferService
            ->startPayment(
                $customer,
                (int)$request->amount
            );


        return redirect()->away(
            $response['gateway']['redirect_url']
        );
    }


public function transactions(Request $request)
{
    $customer = auth()->user()->customer;

    $account = $customer->accounts()
        ->where(
            'account_type',
            \App\Enums\AccountType::SAVING->value
        )
        ->where(
            'status',
            \App\Enums\AccountStatus::ACTIVE->value
        )
        ->firstOrFail();


    /*
    |--------------------------------------------------------------------------
    | Query تراکنش‌ها
    | جدیدترین → قدیمی‌ترین
    |--------------------------------------------------------------------------
    */

    $query = $account->transactions()
        ->orderByDesc('transaction_date')
        ->orderByDesc('id');


    /*
    |--------------------------------------------------------------------------
    | جستجوی شماره تراکنش
    |--------------------------------------------------------------------------
    */

    if ($request->filled('transaction_no')) {

        $query->where(
            'transaction_no',
            'like',
            '%' . trim($request->transaction_no) . '%'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | نوع تراکنش
    |--------------------------------------------------------------------------
    */

    if (
        $request->filled('transaction_type')
        && $request->transaction_type !== 'all'
    ) {

        $query->where(
            'transaction_type',
            $request->transaction_type
        );

    }


    /*
    |--------------------------------------------------------------------------
    | مبلغ
    |--------------------------------------------------------------------------
    */

    if ($request->filled('amount')) {

        $amount = trim($request->amount);

        // تبدیل اعداد فارسی و عربی به انگلیسی
        $amount = strtr($amount, [
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

        // حذف جداکننده‌های هزارگان
        $amount = str_replace(
            [',', '٬', ' '],
            '',
            $amount
        );

        // فقط عدد معتبر پذیرفته شود
        if (ctype_digit($amount)) {

            $query->where(
                'amount',
                (int) $amount
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | از تاریخ
    |--------------------------------------------------------------------------
    */

    if ($request->filled('from_date')) {

        try {

            $fromDate = \Morilog\Jalali\Jalalian::fromFormat(
                'Y/m/d',
                trim($request->from_date)
            )->toCarbon()->format('Y-m-d');

            $query->whereDate(
                'transaction_date',
                '>=',
                $fromDate
            );

        } catch (\Throwable $e) {

            // تاریخ نامعتبر است؛ فیلتر تاریخ اعمال نمی‌شود.

        }

    }


    /*
    |--------------------------------------------------------------------------
    | تا تاریخ
    |--------------------------------------------------------------------------
    */

    if ($request->filled('to_date')) {

        try {

            $toDate = \Morilog\Jalali\Jalalian::fromFormat(
                'Y/m/d',
                trim($request->to_date)
            )->toCarbon()->format('Y-m-d');

            $query->whereDate(
                'transaction_date',
                '<=',
                $toDate
            );

        } catch (\Throwable $e) {

            // تاریخ نامعتبر است؛ فیلتر تاریخ اعمال نمی‌شود.

        }

    }


    /*
    |--------------------------------------------------------------------------
    | صفحه‌بندی
    |--------------------------------------------------------------------------
    */

    $transactions = $query
        ->paginate(20)
        ->withQueryString();


    return view(
        'customer.savings.transactions',
        compact(
            'account',
            'transactions'
        )
    );
}




}
