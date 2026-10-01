<?php

namespace Tests\Feature\Payment;

use App\Enums\InstallmentInterval;
use App\Enums\InstallmentStatus;
use App\Enums\LoanStatus;
use App\Enums\LoanTypeStatus;
use App\Models\Installment;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\LoanType;
use App\Models\PaymentIntent;
use App\Models\User;
use App\Services\Payment\Gateways\GatewayInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuccessfulInstallmentPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_installment_payment(): void
    {
        $user = User::factory()->customer()->create();

        $this->actingAs($user);

        $loanType = LoanType::create([
            'name' => 'وام تست',
            'prefix' => 'TEST',
            'description' => null,
            'status' => LoanTypeStatus::ACTIVE,
        ]);

        $loan = Loan::create([
            'customer_id' => $user->customer_id,
            'loan_type_id' => $loanType->id,
            'loan_number' => 'TEST-0001',
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

        $gateway = $this->mock(GatewayInterface::class);

        $gateway->shouldReceive('request')
            ->once()
            ->andReturn([
                'success' => true,
                'token' => 'TEST-GATEWAY-TOKEN',
                'redirect_url' => 'https://fake-gateway.test/pay/TEST-GATEWAY-TOKEN',
            ]);

        $response = $this->post(
            route('payments.pay', $installment)
        );

        $response->assertRedirect(
            'https://fake-gateway.test/pay/TEST-GATEWAY-TOKEN'
        );

        $this->assertDatabaseHas('payment_intents', [
            'payment_type' => 'installment',
            'reference_id' => $installment->id,
            'payer_user_id' => $user->id,
            'amount' => 1_000_000,
            'gateway_token' => 'TEST-GATEWAY-TOKEN',
        ]);

        $paymentIntent = PaymentIntent::query()
            ->where('reference_id', $installment->id)
            ->latest('id')
            ->firstOrFail();

        $gateway->shouldReceive('verify')
            ->once()
            ->andReturn([
                'success' => true,
                'transaction_id' => 'TEST-TRANSACTION-001',
                'reference_number' => 'TEST-REF-001',
            ]);

        $response = $this->post(
            route('payments.callback'),
            [
                'payment_intent_id' => $paymentIntent->id,
                'payment_type' => 'installment',
                'token' => 'TEST-GATEWAY-TOKEN',
            ]
        );

        $payment = LoanPayment::query()
            ->where('installment_id', $installment->id)
            ->latest('id')
            ->firstOrFail();

        $response->assertRedirect(
            route('customer.installments.payment.success', $payment)
        );

        $this->assertDatabaseHas('loan_payments', [
            'loan_id' => $loan->id,
            'installment_id' => $installment->id,
            'user_id' => $user->id,
            'amount' => 1_000_000,
            'tracking_code' => $paymentIntent->tracking_code,
            'bank_transaction_id' => 'TEST-TRANSACTION-001',
            'bank_reference_number' => 'TEST-REF-001',
        ]);

        $this->assertDatabaseHas('installments', [
            'id' => $installment->id,
            'status' => InstallmentStatus::PAID->value,
        ]);

        $this->assertNotNull(
            Installment::findOrFail($installment->id)->paid_at
        );

        $this->assertDatabaseHas('payment_intents', [
            'id' => $paymentIntent->id,
            'status' => 'paid',
            'gateway_transaction_id' => 'TEST-TRANSACTION-001',
            'gateway_reference_number' => 'TEST-REF-001',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'loan_payment',
            'title' => 'پرداخت قسط',
            'message' => 'قسط شماره 1 با موفقیت پرداخت شد.',
        ]);

        $notification = \App\Models\Notification::query()
            ->where('user_id', $user->id)
            ->where('type', 'loan_payment')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame(
            $payment->id,
            $notification->data['loan_payment_id']
        );

        $this->assertSame(
            $installment->id,
            $notification->data['installment_id']
        );
    }

    public function test_successful_installment_payment_for_another_customer(): void
    {
        $payer = User::factory()->customer()->create();
        $loanOwner = User::factory()->customer()->create();

        $this->actingAs($payer);

        $loanType = LoanType::create([
            'name' => 'وام تست',
            'prefix' => 'TEST',
            'description' => null,
            'status' => LoanTypeStatus::ACTIVE,
        ]);

        $loan = Loan::create([
            'customer_id' => $loanOwner->customer_id,
            'loan_type_id' => $loanType->id,
            'loan_number' => 'TEST-0002',
            'loan_amount' => 10_000_000,
            'installment_amount' => 1_000_000,
            'installment_count' => 10,
            'installment_interval' => InstallmentInterval::MONTHLY,
            'start_date' => now()->toDateString(),
            'first_due_date' => now()->addMonth()->toDateString(),
            'last_due_date' => now()->addMonths(10)->toDateString(),
            'status' => LoanStatus::ACTIVE,
            'description' => null,
            'created_by' => $loanOwner->id,
            'updated_by' => $loanOwner->id,
        ]);

        $installment = Installment::create([
            'loan_id' => $loan->id,
            'installment_number' => 1,
            'amount' => 1_000_000,
            'due_date' => now()->addMonth()->toDateString(),
            'status' => InstallmentStatus::PENDING,
            'paid_at' => null,
            'description' => null,
            'created_by' => $loanOwner->id,
            'updated_by' => $loanOwner->id,
        ]);

        $gateway = $this->mock(GatewayInterface::class);

        $gateway->shouldReceive('request')
            ->once()
            ->andReturn([
                'success' => true,
                'token' => 'TEST-OTHER-GATEWAY-TOKEN',
                'redirect_url' => 'https://fake-gateway.test/pay/TEST-OTHER-GATEWAY-TOKEN',
            ]);

        $response = $this->post(
            route('customer.installments.others.pay'),
            [
                'installment_id' => $installment->id,
            ]
        );

        $response->assertRedirect(
            'https://fake-gateway.test/pay/TEST-OTHER-GATEWAY-TOKEN'
        );

        $this->assertDatabaseHas('payment_intents', [
            'payment_type' => 'installment_other',
            'reference_id' => $installment->id,
            'payer_user_id' => $payer->id,
            'amount' => 1_000_000,
            'gateway_token' => 'TEST-OTHER-GATEWAY-TOKEN',
        ]);

        $paymentIntent = PaymentIntent::query()
            ->where('reference_id', $installment->id)
            ->latest('id')
            ->firstOrFail();

        $gateway->shouldReceive('verify')
            ->once()
            ->andReturn([
                'success' => true,
                'transaction_id' => 'TEST-OTHER-TRANSACTION-001',
                'reference_number' => 'TEST-OTHER-REF-001',
            ]);

        $response = $this->post(
            route('payments.callback'),
            [
                'payment_intent_id' => $paymentIntent->id,
                'payment_type' => 'installment_other',
                'token' => 'TEST-OTHER-GATEWAY-TOKEN',
            ]
        );

        $payment = LoanPayment::query()
            ->where('installment_id', $installment->id)
            ->latest('id')
            ->firstOrFail();

        $response->assertRedirect(
            route('customer.installments.others.payment.success', $payment)
        );

        $this->assertDatabaseHas('loan_payments', [
            'loan_id' => $loan->id,
            'installment_id' => $installment->id,
            'user_id' => $payer->id,
            'amount' => 1_000_000,
            'tracking_code' => $paymentIntent->tracking_code,
            'bank_transaction_id' => 'TEST-OTHER-TRANSACTION-001',
            'bank_reference_number' => 'TEST-OTHER-REF-001',
        ]);

        $this->assertDatabaseHas('installments', [
            'id' => $installment->id,
            'status' => InstallmentStatus::PAID->value,
        ]);

        $this->assertDatabaseHas('payment_intents', [
            'id' => $paymentIntent->id,
            'status' => 'paid',
            'gateway_transaction_id' => 'TEST-OTHER-TRANSACTION-001',
            'gateway_reference_number' => 'TEST-OTHER-REF-001',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $loanOwner->id,
            'type' => 'loan_payment',
            'title' => 'پرداخت قسط',
            'message' => 'قسط شماره 1 با موفقیت پرداخت شد.',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $payer->id,
            'type' => 'loan_payment',
            'title' => 'پرداخت قسط',
            'message' => 'پرداخت قسط با موفقیت انجام شد.',
        ]);

        $ownerNotification = \App\Models\Notification::query()
            ->where('user_id', $loanOwner->id)
            ->where('type', 'loan_payment')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame(
            $payment->id,
            $ownerNotification->data['loan_payment_id']
        );

        $this->assertSame(
            $installment->id,
            $ownerNotification->data['installment_id']
        );

        $payerNotification = \App\Models\Notification::query()
            ->where('user_id', $payer->id)
            ->where('type', 'loan_payment')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame(
            $payment->id,
            $payerNotification->data['loan_payment_id']
        );

        $this->assertSame(
            $installment->id,
            $payerNotification->data['installment_id']
        );
    }

    public function test_failed_gateway_payment_does_not_pay_installment(): void
    {
        $user = User::factory()->customer()->create();

        $this->actingAs($user);

        $loanType = LoanType::create([
            'name' => 'وام تست',
            'prefix' => 'TEST',
            'description' => null,
            'status' => LoanTypeStatus::ACTIVE,
        ]);

        $loan = Loan::create([
            'customer_id' => $user->customer_id,
            'loan_type_id' => $loanType->id,
            'loan_number' => 'TEST-0003',
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

        $gateway = $this->mock(GatewayInterface::class);

        $gateway->shouldReceive('request')
            ->once()
            ->andReturn([
                'success' => true,
                'token' => 'TEST-FAILED-GATEWAY-TOKEN',
                'redirect_url' => 'https://fake-gateway.test/pay/TEST-FAILED-GATEWAY-TOKEN',
            ]);

        $response = $this->post(
            route('payments.pay', $installment)
        );

        $response->assertRedirect(
            'https://fake-gateway.test/pay/TEST-FAILED-GATEWAY-TOKEN'
        );

        $paymentIntent = PaymentIntent::query()
            ->where('reference_id', $installment->id)
            ->latest('id')
            ->firstOrFail();

        $gateway->shouldReceive('verify')
            ->once()
            ->andReturn([
                'success' => false,
                'transaction_id' => null,
                'reference_number' => null,
            ]);

        $response = $this->post(
            route('payments.callback'),
            [
                'payment_intent_id' => $paymentIntent->id,
                'payment_type' => 'installment',
                'token' => 'TEST-FAILED-GATEWAY-TOKEN',
            ]
        );

        $response->assertRedirect(route('payments.failed'));

        $this->assertDatabaseHas('installments', [
            'id' => $installment->id,
            'status' => InstallmentStatus::PENDING->value,
        ]);

        $this->assertNull(
            Installment::findOrFail($installment->id)->paid_at
        );

        $this->assertDatabaseMissing('loan_payments', [
            'installment_id' => $installment->id,
        ]);

        $this->assertDatabaseMissing('payment_intents', [
            'id' => $paymentIntent->id,
            'status' => 'paid',
        ]);
    }

    public function test_invalid_callback_token_does_not_pay_installment(): void
    {
        $user = User::factory()->customer()->create();

        $this->actingAs($user);

        $loanType = LoanType::create([
            'name' => 'وام تست',
            'prefix' => 'TEST',
            'description' => null,
            'status' => LoanTypeStatus::ACTIVE,
        ]);

        $loan = Loan::create([
            'customer_id' => $user->customer_id,
            'loan_type_id' => $loanType->id,
            'loan_number' => 'TEST-0004',
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

        $gateway = $this->mock(GatewayInterface::class);

        $gateway->shouldReceive('request')
            ->once()
            ->andReturn([
                'success' => true,
                'token' => 'TEST-VALID-TOKEN',
                'redirect_url' => 'https://fake-gateway.test/pay/TEST-VALID-TOKEN',
            ]);

        $response = $this->post(
            route('payments.pay', $installment)
        );

        $response->assertRedirect(
            'https://fake-gateway.test/pay/TEST-VALID-TOKEN'
        );

        $paymentIntent = PaymentIntent::query()
            ->where('reference_id', $installment->id)
            ->latest('id')
            ->firstOrFail();

        $gateway->shouldReceive('verify')->never();

        $response = $this->post(
            route('payments.callback'),
            [
                'payment_intent_id' => $paymentIntent->id,
                'payment_type' => 'installment',
                'token' => 'TEST-INVALID-TOKEN',
            ]
        );

        $response->assertRedirect(route('payments.failed'));

        $this->assertDatabaseHas('installments', [
            'id' => $installment->id,
            'status' => InstallmentStatus::PENDING->value,
        ]);

        $this->assertNull(
            Installment::findOrFail($installment->id)->paid_at
        );

        $this->assertDatabaseMissing('loan_payments', [
            'installment_id' => $installment->id,
        ]);

        $this->assertDatabaseMissing('payment_intents', [
            'id' => $paymentIntent->id,
            'status' => 'paid',
        ]);
    }

    public function test_paid_installment_cannot_be_paid_again(): void
    {
        $user = User::factory()->customer()->create();

        $this->actingAs($user);

        $loanType = LoanType::create([
            'name' => 'وام تست',
            'prefix' => 'TEST',
            'description' => null,
            'status' => LoanTypeStatus::ACTIVE,
        ]);

        $loan = Loan::create([
            'customer_id' => $user->customer_id,
            'loan_type_id' => $loanType->id,
            'loan_number' => 'TEST-0005',
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

        Installment::create([
            'loan_id' => $loan->id,
            'installment_number' => 2,
            'amount' => 1_000_000,
            'due_date' => now()->addMonths(2)->toDateString(),
            'status' => InstallmentStatus::PENDING,
            'paid_at' => null,
            'description' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $gateway = $this->mock(GatewayInterface::class);

        $gateway->shouldReceive('request')
            ->once()
            ->andReturn([
                'success' => true,
                'token' => 'TEST-FIRST-PAYMENT-TOKEN',
                'redirect_url' => 'https://fake-gateway.test/pay/TEST-FIRST-PAYMENT-TOKEN',
            ]);

        $response = $this->post(
            route('payments.pay', $installment)
        );

        $response->assertRedirect(
            'https://fake-gateway.test/pay/TEST-FIRST-PAYMENT-TOKEN'
        );

        $paymentIntent = PaymentIntent::query()
            ->where('reference_id', $installment->id)
            ->latest('id')
            ->firstOrFail();

        $gateway->shouldReceive('verify')
            ->once()
            ->andReturn([
                'success' => true,
                'transaction_id' => 'TEST-FIRST-TRANSACTION',
                'reference_number' => 'TEST-FIRST-REFERENCE',
            ]);

        $response = $this->post(
            route('payments.callback'),
            [
                'payment_intent_id' => $paymentIntent->id,
                'payment_type' => 'installment',
                'token' => 'TEST-FIRST-PAYMENT-TOKEN',
            ]
        );

        $payment = LoanPayment::query()
            ->where('installment_id', $installment->id)
            ->firstOrFail();

        $response->assertRedirect(
            route('customer.installments.payment.success', $payment)
        );

        $this->assertDatabaseHas('installments', [
            'id' => $installment->id,
            'status' => InstallmentStatus::PAID->value,
        ]);

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => LoanStatus::ACTIVE->value,
        ]);

        $this->assertSame(
            1,
            LoanPayment::query()
                ->where('installment_id', $installment->id)
                ->count()
        );

        $secondResponse = $this->post(
            route('payments.pay', $installment)
        );

        $secondResponse->assertRedirect('/');

        $this->assertSame(
            1,
            LoanPayment::query()
                ->where('installment_id', $installment->id)
                ->count()
        );

        $this->assertDatabaseHas('installments', [
            'id' => $installment->id,
            'status' => InstallmentStatus::PAID->value,
        ]);
    }

    public function test_cannot_pay_next_installment_before_previous_installment(): void
    {
        $user = User::factory()->customer()->create();

        $this->actingAs($user);

        $loanType = LoanType::create([
            'name' => 'وام تست',
            'prefix' => 'TEST',
            'description' => null,
            'status' => LoanTypeStatus::ACTIVE,
        ]);

        $loan = Loan::create([
            'customer_id' => $user->customer_id,
            'loan_type_id' => $loanType->id,
            'loan_number' => 'TEST-0006',
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

        $firstInstallment = Installment::create([
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

        $secondInstallment = Installment::create([
            'loan_id' => $loan->id,
            'installment_number' => 2,
            'amount' => 1_000_000,
            'due_date' => now()->addMonths(2)->toDateString(),
            'status' => InstallmentStatus::PENDING,
            'paid_at' => null,
            'description' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $gateway = $this->mock(GatewayInterface::class);

        $gateway->shouldReceive('request')->never();

        $response = $this->post(
            route('payments.pay', $secondInstallment)
        );

        $response->assertRedirect('/');

        $this->assertDatabaseHas('installments', [
            'id' => $firstInstallment->id,
            'status' => InstallmentStatus::PENDING->value,
        ]);

        $this->assertDatabaseHas('installments', [
            'id' => $secondInstallment->id,
            'status' => InstallmentStatus::PENDING->value,
        ]);

        $this->assertDatabaseMissing('loan_payments', [
            'installment_id' => $secondInstallment->id,
        ]);

        $this->assertDatabaseMissing('payment_intents', [
            'reference_id' => $secondInstallment->id,
        ]);
    }

    public function test_cannot_pay_installment_when_loan_is_not_active(): void
    {
        $user = User::factory()
            ->customer()
            ->create();

        $this->actingAs($user);

        $loanType = LoanType::create([
            'name' => 'وام تست',
            'prefix' => 'TEST',
            'description' => null,
            'status' => LoanTypeStatus::ACTIVE,
        ]);

        $loan = Loan::create([
            'customer_id' => $user->customer_id,
            'loan_type_id' => $loanType->id,
            'loan_number' => 'TEST-0007',
            'loan_amount' => 10_000_000,
            'installment_amount' => 1_000_000,
            'installment_count' => 10,
            'installment_interval' => InstallmentInterval::MONTHLY,
            'start_date' => now()->toDateString(),
            'first_due_date' => now()->addMonth()->toDateString(),
            'last_due_date' => now()->addMonths(10)->toDateString(),
            'status' => LoanStatus::FINISHED,
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

        $gateway = $this->mock(GatewayInterface::class);

        $gateway->shouldReceive('request')->never();

        $response = $this->post(
            route('payments.pay', $installment)
        );

        $response->assertRedirect('/');

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => LoanStatus::FINISHED->value,
        ]);

        $this->assertDatabaseHas('installments', [
            'id' => $installment->id,
            'status' => InstallmentStatus::PENDING->value,
        ]);

        $this->assertDatabaseMissing('loan_payments', [
            'installment_id' => $installment->id,
        ]);

        $this->assertDatabaseMissing('payment_intents', [
            'reference_id' => $installment->id,
        ]);
    }

    public function test_invalid_callback_amount_does_not_pay_installment(): void
    {
        $user = User::factory()
            ->customer()
            ->create();

        $this->actingAs($user);

        $loanType = LoanType::create([
            'name' => 'وام تست',
            'prefix' => 'TEST',
            'description' => null,
            'status' => LoanTypeStatus::ACTIVE,
        ]);

        $loan = Loan::create([
            'customer_id' => $user->customer_id,
            'loan_type_id' => $loanType->id,
            'loan_number' => 'TEST-0008',
            'loan_amount' => 10_000_000,
            'installment_amount' => 1_000_000,
            'installment_count' => 2,
            'installment_interval' => InstallmentInterval::MONTHLY,
            'start_date' => now()->toDateString(),
            'first_due_date' => now()->addMonth()->toDateString(),
            'last_due_date' => now()->addMonths(2)->toDateString(),
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

        $gateway = $this->mock(GatewayInterface::class);

        $gateway->shouldReceive('request')
            ->once()
            ->andReturn([
                'success' => true,
                'token' => 'TEST-TOKEN-008',
                'redirect_url' => '/fake-gateway/TEST-TOKEN-008',
            ]);

        $response = $this->post(
            route('payments.pay', $installment)
        );

        $response->assertRedirect(
            '/fake-gateway/TEST-TOKEN-008'
        );

        $paymentIntent = PaymentIntent::query()
            ->where('reference_id', $installment->id)
            ->latest('id')
            ->firstOrFail();

        $gateway->shouldReceive('verify')->never();

        $response = $this->post(
            route('payments.callback'),
            [
                'payment_intent_id' => $paymentIntent->id,
                'token' => 'TEST-TOKEN-008',
                'amount' => 100_000,
            ]
        );

        $response->assertRedirect(
            route('payments.failed')
        );

        $this->assertDatabaseHas('installments', [
            'id' => $installment->id,
            'status' => InstallmentStatus::PENDING->value,
        ]);

        $this->assertDatabaseMissing('loan_payments', [
            'installment_id' => $installment->id,
        ]);

        $paymentIntent->refresh();

        $this->assertNotSame(
            'paid',
            $paymentIntent->status
        );
    }

    public function test_callback_token_cannot_be_used_for_another_payment_intent(): void
    {
        $user = User::factory()
            ->customer()
            ->create();

        $this->actingAs($user);

        $loanType = LoanType::create([
            'name' => 'وام تست',
            'prefix' => 'TEST',
            'description' => null,
            'status' => LoanTypeStatus::ACTIVE,
        ]);

        $loan1 = Loan::create([
            'customer_id' => $user->customer_id,
            'loan_type_id' => $loanType->id,
            'loan_number' => 'TEST-0009-A',
            'loan_amount' => 10_000_000,
            'installment_amount' => 1_000_000,
            'installment_count' => 2,
            'installment_interval' => InstallmentInterval::MONTHLY,
            'start_date' => now()->toDateString(),
            'first_due_date' => now()->addMonth()->toDateString(),
            'last_due_date' => now()->addMonths(2)->toDateString(),
            'status' => LoanStatus::ACTIVE,
            'description' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $loan2 = Loan::create([
            'customer_id' => $user->customer_id,
            'loan_type_id' => $loanType->id,
            'loan_number' => 'TEST-0009-B',
            'loan_amount' => 20_000_000,
            'installment_amount' => 2_000_000,
            'installment_count' => 2,
            'installment_interval' => InstallmentInterval::MONTHLY,
            'start_date' => now()->toDateString(),
            'first_due_date' => now()->addMonth()->toDateString(),
            'last_due_date' => now()->addMonths(2)->toDateString(),
            'status' => LoanStatus::ACTIVE,
            'description' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $installment1 = Installment::create([
            'loan_id' => $loan1->id,
            'installment_number' => 1,
            'amount' => 1_000_000,
            'due_date' => now()->addMonth()->toDateString(),
            'status' => InstallmentStatus::PENDING,
            'paid_at' => null,
            'description' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $installment2 = Installment::create([
            'loan_id' => $loan2->id,
            'installment_number' => 1,
            'amount' => 2_000_000,
            'due_date' => now()->addMonth()->toDateString(),
            'status' => InstallmentStatus::PENDING,
            'paid_at' => null,
            'description' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $gateway = $this->mock(GatewayInterface::class);

        $gateway->shouldReceive('request')
            ->twice()
            ->andReturn(
                [
                    'success' => true,
                    'token' => 'TEST-TOKEN-009-A',
                    'redirect_url' => '/fake-gateway/TEST-TOKEN-009-A',
                ],
                [
                    'success' => true,
                    'token' => 'TEST-TOKEN-009-B',
                    'redirect_url' => '/fake-gateway/TEST-TOKEN-009-B',
                ]
            );

        $this->post(
            route('payments.pay', $installment1)
        )->assertRedirect(
            '/fake-gateway/TEST-TOKEN-009-A'
        );

        $this->post(
            route('payments.pay', $installment2)
        )->assertRedirect(
            '/fake-gateway/TEST-TOKEN-009-B'
        );

        $paymentIntentA = PaymentIntent::query()
            ->where('reference_id', $installment1->id)
            ->latest('id')
            ->firstOrFail();

        $paymentIntentB = PaymentIntent::query()
            ->where('reference_id', $installment2->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertNotSame(
            $paymentIntentA->id,
            $paymentIntentB->id
        );

        $this->assertNotSame(
            $paymentIntentA->gateway_token,
            $paymentIntentB->gateway_token
        );

        $gateway->shouldReceive('verify')->never();

        $response = $this->post(
            route('payments.callback'),
            [
                'payment_intent_id' => $paymentIntentB->id,
                'token' => $paymentIntentA->gateway_token,
                'amount' => $installment2->amount,
            ]
        );

        $response->assertRedirect(
            route('payments.failed')
        );

        $this->assertDatabaseMissing('loan_payments', [
            'installment_id' => $installment1->id,
        ]);

        $this->assertDatabaseMissing('loan_payments', [
            'installment_id' => $installment2->id,
        ]);

        $this->assertDatabaseHas('installments', [
            'id' => $installment1->id,
            'status' => InstallmentStatus::PENDING->value,
        ]);

        $this->assertDatabaseHas('installments', [
            'id' => $installment2->id,
            'status' => InstallmentStatus::PENDING->value,
        ]);

        $paymentIntentB->refresh();

        $this->assertNotSame(
            'paid',
            $paymentIntentB->status
        );
    }

    public function test_replayed_successful_callback_does_not_create_duplicate_payment(): void
    {
        $user = User::factory()
            ->customer()
            ->create();

        $this->actingAs($user);

        $loanType = LoanType::create([
            'name' => 'وام تست',
            'prefix' => 'TEST',
            'description' => null,
            'status' => LoanTypeStatus::ACTIVE,
        ]);

        $loan = Loan::create([
            'customer_id' => $user->customer_id,
            'loan_type_id' => $loanType->id,
            'loan_number' => 'TEST-0010',
            'loan_amount' => 10_000_000,
            'installment_amount' => 1_000_000,
            'installment_count' => 2,
            'installment_interval' => InstallmentInterval::MONTHLY,
            'start_date' => now()->toDateString(),
            'first_due_date' => now()->addMonth()->toDateString(),
            'last_due_date' => now()->addMonths(2)->toDateString(),
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

        $gateway = $this->mock(GatewayInterface::class);

        $gateway->shouldReceive('request')
            ->once()
            ->andReturn([
                'success' => true,
                'token' => 'TEST-TOKEN-010',
                'redirect_url' => '/fake-gateway/TEST-TOKEN-010',
            ]);

        $gateway->shouldReceive('verify')
            ->once()
            ->andReturn([
                'success' => true,
                'transaction_id' => 'TX-010',
                'reference_number' => 'REF-010',
            ]);

        $this->post(
            route('payments.pay', $installment)
        )->assertRedirect(
            '/fake-gateway/TEST-TOKEN-010'
        );

        $paymentIntent = PaymentIntent::query()
            ->where('reference_id', $installment->id)
            ->latest('id')
            ->firstOrFail();

        $callbackData = [
            'payment_intent_id' => $paymentIntent->id,
            'payment_type' => 'installment',
            'token' => 'TEST-TOKEN-010',
            'amount' => $installment->amount,
        ];

        $this->post(
            route('payments.callback'),
            $callbackData
        );

        $this->assertDatabaseHas('installments', [
            'id' => $installment->id,
            'status' => InstallmentStatus::PAID->value,
        ]);

        $paymentCount = LoanPayment::query()
            ->where('installment_id', $installment->id)
            ->count();

        $this->assertSame(1, $paymentCount);

        $this->post(
            route('payments.callback'),
            $callbackData
        );

        $paymentCountAfterReplay = LoanPayment::query()
            ->where('installment_id', $installment->id)
            ->count();

        $this->assertSame(
            1,
            $paymentCountAfterReplay
        );

        $this->assertDatabaseHas('installments', [
            'id' => $installment->id,
            'status' => InstallmentStatus::PAID->value,
        ]);

        $paymentIntent->refresh();

        $this->assertSame(
            'paid',
            $paymentIntent->status
        );
    }

    public function test_expired_payment_intent_does_not_pay_installment(): void
    {
        $user = User::factory()
            ->customer()
            ->create();

        $this->actingAs($user);

        $loanType = LoanType::create([
            'name' => 'وام تست',
            'prefix' => 'TEST',
            'description' => null,
            'status' => LoanTypeStatus::ACTIVE,
        ]);

        $loan = Loan::create([
            'customer_id' => $user->customer_id,
            'loan_type_id' => $loanType->id,
            'loan_number' => 'TEST-0011',
            'loan_amount' => 10_000_000,
            'installment_amount' => 1_000_000,
            'installment_count' => 2,
            'installment_interval' => InstallmentInterval::MONTHLY,
            'start_date' => now()->toDateString(),
            'first_due_date' => now()->addMonth()->toDateString(),
            'last_due_date' => now()->addMonths(2)->toDateString(),
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

        $gateway = $this->mock(GatewayInterface::class);

        $gateway->shouldReceive('request')
            ->once()
            ->andReturn([
                'success' => true,
                'token' => 'TEST-TOKEN-011',
                'redirect_url' => '/fake-gateway/TEST-TOKEN-011',
            ]);

        $this->post(
            route('payments.pay', $installment)
        )->assertRedirect(
            '/fake-gateway/TEST-TOKEN-011'
        );

        $paymentIntent = PaymentIntent::query()
            ->where('reference_id', $installment->id)
            ->latest('id')
            ->firstOrFail();

        $paymentIntent->update([
            'expires_at' => now()->subMinute(),
        ]);

        $callbackData = [
            'payment_intent_id' => $paymentIntent->id,
            'payment_type' => 'installment',
            'token' => 'TEST-TOKEN-011',
            'amount' => $installment->amount,
        ];

        $gateway->shouldReceive('verify')
            ->never();

        $this->post(
            route('payments.callback'),
            $callbackData
        );

        $this->assertDatabaseHas('installments', [
            'id' => $installment->id,
            'status' => InstallmentStatus::PENDING->value,
        ]);

        $this->assertDatabaseMissing('loan_payments', [
            'installment_id' => $installment->id,
        ]);

        $paymentIntent->refresh();

        $this->assertNotSame(
            'paid',
            $paymentIntent->status
        );
    }

    public function test_nonexistent_payment_intent_does_not_pay_installment(): void
    {
        $user = User::factory()
            ->customer()
            ->create();

        $this->actingAs($user);

        $loanType = LoanType::create([
            'name' => 'وام تست',
            'prefix' => 'TEST',
            'description' => null,
            'status' => LoanTypeStatus::ACTIVE,
        ]);

        $loan = Loan::create([
            'customer_id' => $user->customer_id,
            'loan_type_id' => $loanType->id,
            'loan_number' => 'TEST-0012',
            'loan_amount' => 10_000_000,
            'installment_amount' => 1_000_000,
            'installment_count' => 2,
            'installment_interval' => InstallmentInterval::MONTHLY,
            'start_date' => now()->toDateString(),
            'first_due_date' => now()->addMonth()->toDateString(),
            'last_due_date' => now()->addMonths(2)->toDateString(),
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

        $gateway = $this->mock(GatewayInterface::class);

        $gateway->shouldReceive('verify')
            ->never();

        $callbackData = [
            'payment_intent_id' => 999999,
            'payment_type' => 'installment',
            'token' => 'TEST-TOKEN-012',
            'amount' => $installment->amount,
        ];

        $response = $this->post(
            route('payments.callback'),
            $callbackData
        );

        $response->assertRedirect(
            route('payments.failed')
        );

        $this->assertDatabaseHas('installments', [
            'id' => $installment->id,
            'status' => InstallmentStatus::PENDING->value,
        ]);

        $this->assertDatabaseMissing('loan_payments', [
            'installment_id' => $installment->id,
        ]);
    }

    public function test_payment_intent_cannot_be_used_for_another_installment(): void
    {
        $user = User::factory()
            ->customer()
            ->create();

        $this->actingAs($user);

        $loanType = LoanType::create([
            'name' => 'وام تست',
            'prefix' => 'TEST',
            'description' => null,
            'status' => LoanTypeStatus::ACTIVE,
        ]);

        $loanA = Loan::create([
            'customer_id' => $user->customer_id,
            'loan_type_id' => $loanType->id,
            'loan_number' => 'TEST-0013-A',
            'loan_amount' => 10_000_000,
            'installment_amount' => 1_000_000,
            'installment_count' => 2,
            'installment_interval' => InstallmentInterval::MONTHLY,
            'start_date' => now()->toDateString(),
            'first_due_date' => now()->addMonth()->toDateString(),
            'last_due_date' => now()->addMonths(2)->toDateString(),
            'status' => LoanStatus::ACTIVE,
            'description' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $installmentA = Installment::create([
            'loan_id' => $loanA->id,
            'installment_number' => 1,
            'amount' => 1_000_000,
            'due_date' => now()->addMonth()->toDateString(),
            'status' => InstallmentStatus::PENDING,
            'paid_at' => null,
            'description' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $loanB = Loan::create([
            'customer_id' => $user->customer_id,
            'loan_type_id' => $loanType->id,
            'loan_number' => 'TEST-0013-B',
            'loan_amount' => 10_000_000,
            'installment_amount' => 1_000_000,
            'installment_count' => 2,
            'installment_interval' => InstallmentInterval::MONTHLY,
            'start_date' => now()->toDateString(),
            'first_due_date' => now()->addMonth()->toDateString(),
            'last_due_date' => now()->addMonths(2)->toDateString(),
            'status' => LoanStatus::ACTIVE,
            'description' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $installmentB = Installment::create([
            'loan_id' => $loanB->id,
            'installment_number' => 1,
            'amount' => 1_000_000,
            'due_date' => now()->addMonth()->toDateString(),
            'status' => InstallmentStatus::PENDING,
            'paid_at' => null,
            'description' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $gateway = $this->mock(GatewayInterface::class);

        $gateway->shouldReceive('request')
            ->once()
            ->andReturn([
                'success' => true,
                'token' => 'TEST-TOKEN-013',
                'redirect_url' => '/fake-gateway/TEST-TOKEN-013',
            ]);

        $this->post(
            route('payments.pay', $installmentA)
        )->assertRedirect(
            '/fake-gateway/TEST-TOKEN-013'
        );

        $paymentIntent = PaymentIntent::query()
            ->where('reference_id', $installmentA->id)
            ->latest('id')
            ->firstOrFail();

        $gateway->shouldReceive('verify')
            ->once()
            ->andReturn([
                'success' => true,
                'transaction_id' => 'TEST-TRANSACTION-013',
                'reference_number' => 'TEST-REFERENCE-013',
            ]);

        $callbackData = [
            'payment_intent_id' => $paymentIntent->id,
            'payment_type' => 'installment',
            'installment_id' => $installmentB->id,
            'token' => 'TEST-TOKEN-013',
            'amount' => $installmentB->amount,
        ];

        $response = $this->post(
            route('payments.callback'),
            $callbackData
        );

        $response->assertRedirect(
            route('customer.installments.payment.success', [
                'payment' => LoanPayment::query()
                    ->where('installment_id', $installmentA->id)
                    ->latest('id')
                    ->firstOrFail(),
            ])
        );

        $this->assertDatabaseHas('installments', [
            'id' => $installmentA->id,
            'status' => InstallmentStatus::PAID->value,
        ]);

        $this->assertDatabaseHas('installments', [
            'id' => $installmentB->id,
            'status' => InstallmentStatus::PENDING->value,
        ]);

        $this->assertDatabaseHas('loan_payments', [
            'installment_id' => $installmentA->id,
        ]);

        $this->assertDatabaseMissing('loan_payments', [
            'installment_id' => $installmentB->id,
        ]);

        $paymentIntent->refresh();

        $this->assertSame(
            'paid',
            $paymentIntent->status
        );

    }

public function test_successful_callback_works_without_authenticated_user(): void
{
    $user = User::factory()
        ->customer()
        ->create();

    $this->actingAs($user);

    $loanType = LoanType::create([
        'name' => 'وام تست',
        'prefix' => 'TEST',
        'description' => null,
        'status' => LoanTypeStatus::ACTIVE,
    ]);

    $loan = Loan::create([
        'customer_id' => $user->customer_id,
        'loan_type_id' => $loanType->id,
        'loan_number' => 'TEST-GUEST-CALLBACK',
        'loan_amount' => 10_000_000,
        'installment_amount' => 1_000_000,
        'installment_count' => 2,
        'installment_interval' => InstallmentInterval::MONTHLY,
        'start_date' => now()->toDateString(),
        'first_due_date' => now()->addMonth()->toDateString(),
        'last_due_date' => now()->addMonths(2)->toDateString(),
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

    $gateway = $this->mock(GatewayInterface::class);

    $gateway->shouldReceive('request')
        ->once()
        ->andReturn([
            'success' => true,
            'token' => 'TEST-GUEST-CALLBACK-TOKEN',
            'redirect_url' => '/fake-gateway/TEST-GUEST-CALLBACK-TOKEN',
        ]);

    $this->post(
        route('payments.pay', $installment)
    )->assertRedirect(
        '/fake-gateway/TEST-GUEST-CALLBACK-TOKEN'
    );

    $paymentIntent = PaymentIntent::query()
        ->where('reference_id', $installment->id)
        ->latest('id')
        ->firstOrFail();

    $gateway->shouldReceive('verify')
        ->once()
        ->andReturn([
            'success' => true,
            'transaction_id' => 'TEST-GUEST-TRANSACTION',
            'reference_number' => 'TEST-GUEST-REFERENCE',
        ]);

    auth()->logout();

    $response = $this->post(
        route('payments.callback'),
        [
            'payment_intent_id' => $paymentIntent->id,
            'payment_type' => 'installment',
            'token' => 'TEST-GUEST-CALLBACK-TOKEN',
        ]
    );

    $this->assertDatabaseHas('loan_payments', [
        'loan_id' => $loan->id,
        'installment_id' => $installment->id,
        'user_id' => $user->id,
        'amount' => 1_000_000,
        'tracking_code' => $paymentIntent->tracking_code,
        'bank_transaction_id' => 'TEST-GUEST-TRANSACTION',
        'bank_reference_number' => 'TEST-GUEST-REFERENCE',
    ]);

    $this->assertDatabaseHas('installments', [
        'id' => $installment->id,
        'status' => InstallmentStatus::PAID->value,
    ]);

    $this->assertDatabaseHas('payment_intents', [
        'id' => $paymentIntent->id,
        'status' => 'paid',
    ]);

    $this->assertNotNull(
        $response->headers->get('Location')
    );
}

}
