<?php

namespace Tests\Feature\Payment;

use App\Enums\Gateway;
use App\Enums\InstallmentInterval;
use App\Enums\InstallmentStatus;
use App\Enums\LoanStatus;
use App\Enums\LoanTypeStatus;
use App\Models\Customer;
use App\Models\DonationPayment;
use App\Models\Installment;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\LoanType;
use App\Models\User;
use App\Services\Payment\PaymentResolverService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use App\Enums\PaymentGateway;
use App\Models\Account;

class PaymentControllerCallbackTest extends TestCase
{
    use RefreshDatabase;

    private function createLoanData(User $user, string $loanNumber): array
    {
        $loanType = LoanType::create([
            'name' => 'وام تست',
            'prefix' => 'TEST',
            'description' => null,
            'status' => LoanTypeStatus::ACTIVE,
        ]);

        $loan = Loan::create([
            'customer_id' => $user->customer_id,
            'loan_type_id' => $loanType->id,
            'loan_number' => $loanNumber,
            'loan_amount' => 10_000_000,
            'installment_amount' => 1_000_000,
            'installment_count' => 10,
            'installment_interval' => InstallmentInterval::MONTHLY,
            'start_date' => now()->toDateString(),
            'first_due_date' => now()->addMonth()->toDateString(),
            'last_due_date' => now()->addMonths(10)->toDateString(),
            'status' => LoanStatus::ACTIVE,
            'description' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $installment = Installment::create([
            'loan_id' => $loan->id,
            'installment_number' => 1,
            'amount' => 1_000_000,
            'due_date' => now()->addMonth()->toDateString(),
            'status' => InstallmentStatus::PENDING,
            'paid_at' => null,
            'description' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return [$loan, $installment];
    }

    public function test_callback_redirects_to_own_installment_success_page(): void
    {
        $user = User::factory()->customer()->create();

        [$loan, $installment] = $this->createLoanData(
            $user,
            'TEST-1001'
        );

        $payment = LoanPayment::create([
            'loan_id' => $loan->id,
            'installment_id' => $installment->id,
            'user_id' => $user->id,
            'amount' => $installment->amount,
            'tracking_code' => 'LPTEST1001',
            'gateway' => PaymentGateway::FAKE,
            'bank_transaction_id' => 'TXTEST1001',
            'bank_reference_number' => 'REFTEST1001',
            'paid_at' => now(),
        ]);

        $this->actingAs($user);

        $resolver = Mockery::mock(PaymentResolverService::class);

        $resolver
            ->shouldReceive('verify')
            ->once()
            ->andReturn($payment);

        $this->app->instance(
            PaymentResolverService::class,
            $resolver
        );

        $response = $this->post(
            route('payments.callback'),
            [
                'payment_type' => 'installment',
                'payment_intent_id' => 100,
                'token' => 'TEST-TOKEN',
            ]
        );

        $response->assertRedirect(
            route(
                'customer.installments.payment.success',
                $payment
            )
        );
    }

    public function test_callback_redirects_to_other_installment_success_page(): void
    {
        $payer = User::factory()->customer()->create();

        $loanOwner = User::factory()->customer()->create();

        [$loan, $installment] = $this->createLoanData(
            $loanOwner,
            'TEST-1002'
        );

        $payment = LoanPayment::create([
            'loan_id' => $loan->id,
            'installment_id' => $installment->id,
            'user_id' => $payer->id,
            'amount' => $installment->amount,
            'tracking_code' => 'LPTEST1002',
            'gateway' => PaymentGateway::FAKE,
            'bank_transaction_id' => 'TXTEST1002',
            'bank_reference_number' => 'REFTEST1002',
            'paid_at' => now(),
        ]);

        $this->actingAs($payer);

        $resolver = Mockery::mock(PaymentResolverService::class);

        $resolver
            ->shouldReceive('verify')
            ->once()
            ->andReturn($payment);

        $this->app->instance(
            PaymentResolverService::class,
            $resolver
        );

        $response = $this->post(
            route('payments.callback'),
            [
                'payment_type' => 'installment_other',
                'payment_intent_id' => 101,
                'token' => 'TEST-TOKEN',
            ]
        );

        $response->assertRedirect(
            route(
                'customer.installments.others.payment.success',
                $payment
            )
        );
    }

    public function test_callback_failure_redirects_to_failed_payment_page(): void
    {
        $resolver = Mockery::mock(PaymentResolverService::class);

        $resolver
            ->shouldReceive('verify')
            ->once()
            ->andThrow(
                new \DomainException('پرداخت ناموفق بود.')
            );

        $this->app->instance(
            PaymentResolverService::class,
            $resolver
        );

        $response = $this->post(
            route('payments.callback'),
            [
                'payment_type' => 'installment',
                'reference_id' => 25,
                'installment_id' => 30,
                'payment_intent_id' => 102,
                'token' => 'INVALID-TOKEN',
            ]
        );

        $response->assertRedirect(
            route(
                'payments.failed',
                [
                    'reference_id' => 25,
                    'installment_id' => 30,
                ]
            )
        );

        $response->assertSessionHas(
            'error',
            'پرداخت با موفقیت تکمیل نشد. لطفاً دوباره تلاش کنید.'
        );
    }

    public function test_callback_with_invalid_payment_type_redirects_to_failed_page(): void
    {
        $resolver = Mockery::mock(PaymentResolverService::class);

        $resolver
            ->shouldReceive('verify')
            ->once()
            ->andThrow(
                new \RuntimeException('نوع پرداخت مشخص نیست.')
            );

        $this->app->instance(
            PaymentResolverService::class,
            $resolver
        );

        $response = $this->post(
            route('payments.callback'),
            [
                'payment_type' => 'invalid_payment_type',
                'reference_id' => 50,
                'installment_id' => 60,
            ]
        );

        $response->assertRedirect(
            route(
                'payments.failed',
                [
                    'reference_id' => 50,
                    'installment_id' => 60,
                ]
            )
        );

        $response->assertSessionHas(
            'error',
            'پرداخت با موفقیت تکمیل نشد. لطفاً دوباره تلاش کنید.'
        );
    }

    public function test_callback_redirects_public_donation_to_success_page(): void
    {
        $account = Account::create([
            'name' => 'حساب تست کمک',
            'account_number' => '6111000001',
            'account_type' => 1,
            'balance' => 0,
            'status' => 1,
            'opened_date' => now()->toDateString(),
        ]);

$payment = DonationPayment::create([
    'customer_id' => null,
    'donor_name' => 'پرداخت‌کننده تست',
    'donor_mobile' => '09120000000',
    'account_id' => $account->id,
    'amount' => 100_000,
    'tracking_code' => 'DPTEST1001',
    'status' => 'paid',
    'gateway' => PaymentGateway::FAKE,
    'bank_transaction_id' => 'TXDONATION1001',
    'bank_reference_number' => 'REFDONATION1001',
    'paid_at' => now(),
]);

$resolver = Mockery::mock(PaymentResolverService::class);

$resolver
    ->shouldReceive('verify')
    ->once()
    ->andReturn($payment);

$this->app->instance(
    PaymentResolverService::class,
    $resolver
);

$response = $this->post(
    route('payments.callback'),
    [
        'payment_type' => 'donation_public',
        'payment_intent_id' => 103,
        'token' => 'TEST-TOKEN',
    ]
);

$response->assertRedirect(
    route(
        'donation.success',
        $payment
    )
);

}

    public function test_guest_callback_redirects_to_installment_success_page(): void
    {
        $loanOwner = User::factory()->customer()->create();

[$loan, $installment] = $this->createLoanData(
    $loanOwner,
    'TEST-1003'
);

$payment = LoanPayment::create([
    'loan_id' => $loan->id,
    'installment_id' => $installment->id,
    'user_id' => $loanOwner->id,
    'amount' => $installment->amount,
    'tracking_code' => 'LPTEST1003',
    'gateway' => PaymentGateway::FAKE,
    'bank_transaction_id' => 'TXTEST1003',
    'bank_reference_number' => 'REFTEST1003',
    'paid_at' => now(),
]);

auth()->logout();

$resolver = Mockery::mock(PaymentResolverService::class);

$resolver
    ->shouldReceive('verify')
    ->once()
    ->andReturn($payment);

$this->app->instance(
    PaymentResolverService::class,
    $resolver
);

$response = $this->post(
    route('payments.callback'),
    [
        'payment_type' => 'installment',
        'payment_intent_id' => 104,
        'token' => 'TEST-TOKEN',
    ]
);

$response->assertRedirect(
    route(
        'customer.installments.payment.success',
        $payment
    )
);

}

    public function test_guest_failed_installment_payment_does_not_crash(): void
    {
        $loanOwner = User::factory()->customer()->create();

[$loan, $installment] = $this->createLoanData(
    $loanOwner,
    'TEST-1004'
);

auth()->logout();

$response = $this->get(
    route('payments.failed') . '?' . http_build_query([
        'payment_type' => 'installment',
        'installment_id' => $installment->id,
        'reference_id' => 104,
    ])
);

$response->assertRedirect(
    route('customer.installments.others.create')
);

$response->assertSessionHas(
    'error',
    'پرداخت قسط انجام نشد.'
);

}


}
