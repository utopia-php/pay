<?php

declare(strict_types=1);

namespace Utopia\Pay\Tests\Adapter;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Utopia\Pay\Adapter\Stripe;
use Utopia\Pay\Address;
use Utopia\Pay\CardDetails;
use Utopia\Pay\Exception;
use Utopia\Pay\Payment\Options;
use Utopia\Pay\Payment\Status;
use Utopia\Pay\Refund\Reason;
use Utopia\Pay\Refund\Status as RefundStatus;

#[Group('stripe')]
class StripeTest extends TestCase
{
    private Stripe $stripe;

    protected function setUp(): void
    {
        $secret = getenv('STRIPE_SECRET');
        if ($secret === false || $secret === '') {
            $this->markTestSkipped('STRIPE_SECRET is required');
        }
        $this->stripe = new Stripe($secret);
    }

    public function testCustomerAndCardLifecycle(): void
    {
        $customer = $this->stripe->createCustomer('Pay test', 'test@example.com', new Address('Kathmandu', 'NP'));
        try {
            $this->assertNotEmpty($customer->id);
            $this->assertSame('Pay test', $this->stripe->getCustomer($customer->id)->name);
            $updated = $this->stripe->updateCustomer($customer->id, 'Updated', 'updated@example.com');
            $this->assertSame('Updated', $updated->name);
            $this->assertSame('updated@example.com', $updated->email);
            $method = $this->stripe->createPaymentMethod($customer->id, new CardDetails('4242424242424242', 8, 2030, '123'));
            $this->assertSame('4242', $method->card?->last4);
            $this->assertSame('visa', $method->card->brand);
            $this->assertSame($method->id, $this->stripe->getPaymentMethod($customer->id, $method->id)->id);
            $updatedMethod = $this->stripe->updatePaymentMethod($method->id, new CardDetails(expiryYear: 2031));
            $this->assertSame(2031, $updatedMethod->card?->expiryYear);
            $this->assertContains($method->id, array_map(static fn ($card) => $card->id, $this->stripe->listPaymentMethods($customer->id)));
            $this->stripe->deletePaymentMethod($method->id);
            $this->assertNotContains($method->id, array_map(static fn ($card) => $card->id, $this->stripe->listPaymentMethods($customer->id)));
        } finally {
            $this->stripe->deleteCustomer($customer->id);
        }
        $this->assertTrue($this->stripe->getCustomer($customer->id)->deleted);
    }

    public function testAuthorizationCaptureCancellationAndRefund(): void
    {
        $customer = $this->stripe->createCustomer('Pay authorization test', 'test@example.com');
        try {
            $method = $this->stripe->createPaymentMethod($customer->id, new CardDetails('4242424242424242', 8, 2030, '123'));
            $hold = $this->stripe->authorize(10000, $customer->id, $method->id, new Options(metadata: ['orderId' => 'test_order']));
            $this->assertSame(Status::RequiresCapture, $hold->status);
            $this->assertSame('test_order', $hold->metadata['orderId']);
            $captured = $this->stripe->capture($hold->id, 6000);
            $this->assertSame(Status::Succeeded, $captured->status);
            $this->assertSame(6000, $captured->amountReceived);
            $refund = $this->stripe->refund($hold->id, 3000, Reason::RequestedByCustomer);
            $this->assertSame(RefundStatus::Succeeded, $refund->status);
            $this->assertSame(3000, $refund->amount);
            $cancel = $this->stripe->authorize(5000, $customer->id, $method->id);
            $this->assertSame(Status::Canceled, $this->stripe->cancelAuthorization($cancel->id)->status);
            $paid = $this->stripe->purchase(5000, $customer->id, $method->id);
            $this->assertSame(Status::Succeeded, $paid->status);
            $this->assertSame(5000, $this->stripe->getPayment($paid->id)->amountReceived);
        } finally {
            $this->stripe->deleteCustomer($customer->id);
        }
    }

    public function testDeclinedChargeRetainsThePaymentForRetry(): void
    {
        $customer = $this->stripe->createCustomer('Pay decline test', 'test@example.com');
        try {
            $method = $this->stripe->createPaymentMethod($customer->id, new CardDetails('4000000000009995', 8, 2030, '123'));
            try {
                $this->stripe->purchase(5000, $customer->id, $method->id);
                $this->fail('The declined charge must throw');
            } catch (Exception $exception) {
                $this->assertSame(Exception::INSUFFICIENT_FUNDS, $exception->type);
                $payment = $exception->error?->payment;
                $this->assertNotNull($payment);
                $this->assertSame(Status::RequiresPaymentMethod, $payment->status);
                $this->assertSame($method->id, $payment->lastPaymentError?->paymentMethodId);
                $working = $this->stripe->createPaymentMethod($customer->id, new CardDetails('4242424242424242', 8, 2030, '123'));
                $updated = $this->stripe->updatePayment($payment->id, $working->id);
                $this->assertSame($working->id, $updated->paymentMethodId);
                $retried = $this->stripe->retryPurchase($payment->id, $working->id, new Options(offSession: true));
                $this->assertSame(Status::Succeeded, $retried->status);
                $this->assertSame(5000, $retried->amountReceived);
            }
        } finally {
            $this->stripe->deleteCustomer($customer->id);
        }
    }
}
