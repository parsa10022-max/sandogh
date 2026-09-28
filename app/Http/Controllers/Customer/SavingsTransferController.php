<?php

namespace App\Http\Controllers\Customer;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Customer;
use App\Services\Savings\SavingsTransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Morilog\Jalali\Jalalian;

class SavingsTransferController extends Controller
{
    public function __construct(
        private readonly SavingsTransferService $savingsTransferService,
    ) {
    }

    /**
     * فرم واریز
     */
    public function create(): View
    {
        return view(
            'customer.savings-transfer.create'
        );
    }

    /**
     * جستجوی حساب پس‌انداز مقصد
     *
     * ورودی‌های قابل قبول:
     *
     * 6111-000011
     * 6111000011
     * 000011
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'keyword' => [
                'required',
                'string',
                'max:50',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | پاکسازی شماره حساب
        |--------------------------------------------------------------------------
        */

        $keyword = trim($request->keyword);

        /*
        |--------------------------------------------------------------------------
        | تبدیل اعداد فارسی و عربی به انگلیسی
        |--------------------------------------------------------------------------
        */

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
        | حذف خط تیره و فاصله و جداکننده‌ها
        |--------------------------------------------------------------------------
        */

        $keyword = str_replace(
            ['-', '–', '—', ' ', '٬', ','],
            '',
            $keyword
        );

        /*
        |--------------------------------------------------------------------------
        | اعتبارسنجی ساختار شماره حساب
        |--------------------------------------------------------------------------
        */

        if (! ctype_digit($keyword)) {
            return response()->json([
                'found' => false,
                'message' => 'شماره حساب نامعتبر است.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | اگر فقط 6 رقم آخر وارد شده باشد
        |--------------------------------------------------------------------------
        */

        if (strlen($keyword) === 6) {
            $keyword = '6111' . $keyword;
        }

        /*
        |--------------------------------------------------------------------------
        | جستجوی حساب پس‌انداز فعال
        |--------------------------------------------------------------------------
        */

        $account = Account::query()
            ->where(
                'account_type',
                AccountType::SAVING->value
            )
            ->where(
                'status',
                AccountStatus::ACTIVE->value
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

        if (! $customer) {
            return response()->json([
                'found' => false,
                'message' =>
                    'صاحب این حساب پیدا نشد.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | نتیجه
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'found' => true,

            'customer' => [
                'id' => $customer->id,

                'name' => $customer->full_name,

                'account_number' =>
                    $account->account_number,
            ],
        ]);
    }

    /**
     * شروع پرداخت به حساب پس‌انداز عضو دیگر
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'amount' => clean_money($request->amount),
        ]);

        $request->validate([
            'receiver_customer_id' => [
                'required',
                'integer',
                'exists:customers,id',
            ],

            'amount' => [
                'required',
                'integer',
                'min:1000',
            ],
        ]);

        $customer = auth()->user()->customer;

        if (! $customer) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | جلوگیری از واریز به حساب خود از مسیر «پرداخت به دیگران»
        |--------------------------------------------------------------------------
        */

        if (
            (int) $request->receiver_customer_id ===
            (int) $customer->id
        ) {
            return back()->with(
                'error',
                'برای واریز به حساب خودتان از بخش واریز به حساب خود استفاده کنید.'
            );
        }

        $receiver = Customer::findOrFail(
            $request->receiver_customer_id
        );

        /*
        |--------------------------------------------------------------------------
        | شروع پرداخت
        |--------------------------------------------------------------------------
        */

        $result = $this->savingsTransferService->startPayment(
            $receiver,
            (int) $request->amount
        );

        return redirect()->away(
            $result['gateway']['redirect_url']
        );
    }

    /**
     * فرم واریز به حساب خود
     */
    public function ownDepositCreate(): View
    {
        $customer = auth()->user()->customer;

        if (! $customer) {
            abort(403);
        }

        $account = $customer->accounts()
            ->where(
                'account_type',
                AccountType::SAVING->value
            )
            ->where(
                'status',
                AccountStatus::ACTIVE->value
            )
            ->firstOrFail();

        return view(
            'customer.savings.deposit.create',
            compact('account')
        );
    }

    /**
     * شروع واریز به حساب خود
     */
    public function ownDepositStore(Request $request): RedirectResponse
    {
        $request->merge([
            'amount' => clean_money($request->amount),
        ]);

        $request->validate([
            'amount' => [
                'required',
                'integer',
                'min:50000',
            ],
        ]);

        $customer = auth()->user()->customer;

        if (! $customer) {
            abort(403);
        }

        $response = $this->savingsTransferService->startPayment(
            $customer,
            (int) $request->amount
        );

        return redirect()->away(
            $response['gateway']['redirect_url']
        );
    }

    /**
     * تراکنش‌های حساب پس‌انداز
     */
    public function transactions(Request $request): View
    {
        $customer = auth()->user()->customer;

        if (! $customer) {
            abort(403);
        }

        $account = $customer->accounts()
            ->where(
                'account_type',
                AccountType::SAVING->value
            )
            ->where(
                'status',
                AccountStatus::ACTIVE->value
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
            $transactionNo = trim($request->transaction_no);

            if ($transactionNo !== '') {
                $query->where(
                    'transaction_no',
                    'like',
                    '%' . $transactionNo . '%'
                );
            }
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

            /*
            | تبدیل اعداد فارسی و عربی
            */

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

            /*
            | حذف جداکننده‌های هزارگان
            */

            $amount = str_replace(
                [',', '٬', ' '],
                '',
                $amount
            );

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
                $fromDate = Jalalian::fromFormat(
                    'Y/m/d',
                    trim($request->from_date)
                )
                    ->toCarbon()
                    ->format('Y-m-d');

                $query->whereDate(
                    'transaction_date',
                    '>=',
                    $fromDate
                );
            } catch (\Throwable) {
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
                $toDate = Jalalian::fromFormat(
                    'Y/m/d',
                    trim($request->to_date)
                )
                    ->toCarbon()
                    ->format('Y-m-d');

                $query->whereDate(
                    'transaction_date',
                    '<=',
                    $toDate
                );
            } catch (\Throwable) {
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
