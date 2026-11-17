<?php

namespace App\Http\Controllers\Customer;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\DonationPayment;
use App\Services\Donation\DonationPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DonationController extends Controller
{
    public function __construct(
        private readonly DonationPaymentService $donationPaymentService
    )
    {
    }

    /**
     * فرم ثبت کمک
     */
    public function create(Request $request): View|RedirectResponse
    {
        $accounts = Account::query()
            ->where('account_type', AccountType::SYSTEM)
            ->where('status', AccountStatus::ACTIVE)
            ->orderBy('account_number')
            ->get();

        $selectedAccountId = $request->integer('account_id');

        if (!$selectedAccountId) {
            return redirect()
                ->route('customer.dashboard')
                ->with(
                    'error',
                    'لطفاً ابتدا حساب مقصد کمک را انتخاب کنید.'
                );
        }

        $selectedAccount = $accounts->firstWhere(
            'id',
            $selectedAccountId
        );

        if (!$selectedAccount) {
            return redirect()
                ->route('customer.dashboard')
                ->with(
                    'error',
                    'حساب مقصد انتخاب‌شده معتبر نیست.'
                );
        }

        return view(
            'customer.donations.create',
            compact(
                'accounts',
                'selectedAccountId',
                'selectedAccount'
            )
        );
    }

    /**
     * ثبت کمک
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'amount' => clean_money($request->input('amount')),
        ]);

        $validated = $request->validate(
            [
                'account_id' => ['required', 'integer', 'exists:accounts,id'],
                'amount' => ['required', 'integer', 'min:50000'],
            ],
            [
                'account_id.required' => 'انتخاب حساب مقصد الزامی است.',
                'account_id.integer' => 'حساب مقصد انتخاب‌شده معتبر نیست.',
                'account_id.exists' => 'حساب مقصد انتخاب‌شده وجود ندارد.',
                'amount.required' => 'مبلغ کمک الزامی است.',
                'amount.integer' => 'مبلغ کمک باید به‌صورت عدد وارد شود.',
                'amount.min' => 'حداقل مبلغ کمک ۵۰٬۰۰۰ ریال است.',
            ]
        );

        $customer = auth()->user()->customer;

        abort_if(
            !$customer,
            403,
            'دسترسی به اطلاعات مشتری امکان‌پذیر نیست.'
        );

        $account = Account::query()
            ->where('account_type', AccountType::SYSTEM)
            ->where('status', AccountStatus::ACTIVE)
            ->findOrFail($validated['account_id']);

        $result = $this->donationPaymentService->startPayment(
            customer: $customer,
            account: $account,
            amount: (int)$validated['amount'],
    );

        $redirectUrl = $result['gateway']['redirect_url'] ?? null;

        abort_if(
            ! is_string($redirectUrl)
            || trim($redirectUrl) === '',
            502,
            'آدرس انتقال به درگاه پرداخت معتبر نیست.'
        );

        return redirect()->away($redirectUrl);
    }

    /**
     * صفحه پرداخت کمک
     */
    public function payment(
        DonationPayment $donationPayment
    ): View {
        $customerId = auth()->user()->customer?->id;

        abort_if(
            $customerId === null
            || (int) $donationPayment->customer_id !== (int) $customerId,
            403
        );

        return view(
            'customer.donations.payment',
            compact('donationPayment')
        );
    }

    /**
     * ارسال کمک به درگاه
     */
    public function pay(
        DonationPayment $donationPayment
    ): RedirectResponse {
        $customerId = auth()->user()->customer?->id;

        abort_if(
            $customerId === null
            || (int) $donationPayment->customer_id !== (int) $customerId,
            403
        );

        $response = $this->donationPaymentService
            ->sendToGateway($donationPayment);

        $redirectUrl = $response['gateway']['redirect_url'] ?? null;

        abort_if(
            ! is_string($redirectUrl)
            || trim($redirectUrl) === '',
            502,
            'آدرس انتقال به درگاه پرداخت معتبر نیست.'
        );

        return redirect()->away($redirectUrl);
    }

    /**
     * صفحه موفقیت پرداخت
     */
    public function success(
        DonationPayment $donationPayment
    ): View
    {
        $customerId = auth()->user()->customer?->id;

        abort_if(
            !$customerId
            || (int)$donationPayment->customer_id !== (int)$customerId,
            403
        );

        abort_unless(
            (int)$donationPayment->status === 1,
            404
        );

        return view(
            'customer.donations.success',
            compact('donationPayment')
        );
    }
}
