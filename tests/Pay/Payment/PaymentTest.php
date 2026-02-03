<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Payment\Payment;

class PaymentTest extends TestCase
{
    private Payment $payment;

    protected function setUp(): void
    {
        $this->payment = new Payment(
            'pi_123',
            1000, // $10.00
            'USD',
            Payment::STATUS_SUCCEEDED
        );
    }

    public function testConstructor(): void
    {
        $this->assertEquals('pi_123', $this->payment->getId());
        $this->assertEquals(1000, $this->payment->getAmount());
        $this->assertEquals('USD', $this->payment->getCurrency());
        $this->assertEquals(Payment::STATUS_SUCCEEDED, $this->payment->getStatus());
        $this->assertNotNull($this->payment->getCreatedAt());
    }

    public function testConstructorWithAllParameters(): void
    {
        $payment = new Payment(
            'pi_full',
            5000,
            'EUR',
            Payment::STATUS_PROCESSING,
            'cus_123',
            'pm_123',
            'Test payment',
            4500,
            500,
            'pi_full_secret_123',
            'ch_123',
            'test@example.com',
            'https://receipt.stripe.com/123',
            null,
            null,
            ['order_id' => 'order_123'],
            1234567890
        );

        $this->assertEquals('pi_full', $payment->getId());
        $this->assertEquals(5000, $payment->getAmount());
        $this->assertEquals('EUR', $payment->getCurrency());
        $this->assertEquals(Payment::STATUS_PROCESSING, $payment->getStatus());
        $this->assertEquals('cus_123', $payment->getCustomerId());
        $this->assertEquals('pm_123', $payment->getPaymentMethodId());
        $this->assertEquals('Test payment', $payment->getDescription());
        $this->assertEquals(4500, $payment->getAmountReceived());
        $this->assertEquals(500, $payment->getAmountRefunded());
        $this->assertEquals('pi_full_secret_123', $payment->getClientSecret());
        $this->assertEquals('ch_123', $payment->getChargeId());
        $this->assertEquals('test@example.com', $payment->getReceiptEmail());
        $this->assertEquals('https://receipt.stripe.com/123', $payment->getReceiptUrl());
        $this->assertEquals(['order_id' => 'order_123'], $payment->getMetadata());
        $this->assertEquals(1234567890, $payment->getCreatedAt());
    }

    public function testGettersAndSetters(): void
    {
        $this->payment->setId('pi_new');
        $this->payment->setAmount(2500);
        $this->payment->setCurrency('GBP');
        $this->payment->setStatus(Payment::STATUS_REQUIRES_ACTION);
        $this->payment->setCustomerId('cus_new');
        $this->payment->setPaymentMethodId('pm_new');
        $this->payment->setDescription('New description');
        $this->payment->setAmountReceived(2000);
        $this->payment->setAmountRefunded(500);
        $this->payment->setClientSecret('secret_new');
        $this->payment->setChargeId('ch_new');
        $this->payment->setReceiptEmail('new@example.com');
        $this->payment->setReceiptUrl('https://example.com/receipt');
        $this->payment->setFailureCode('card_declined');
        $this->payment->setFailureMessage('Your card was declined');
        $this->payment->setMetadata(['key' => 'value']);
        $this->payment->setCreatedAt(9876543210);

        $this->assertEquals('pi_new', $this->payment->getId());
        $this->assertEquals(2500, $this->payment->getAmount());
        $this->assertEquals('GBP', $this->payment->getCurrency());
        $this->assertEquals(Payment::STATUS_REQUIRES_ACTION, $this->payment->getStatus());
        $this->assertEquals('cus_new', $this->payment->getCustomerId());
        $this->assertEquals('pm_new', $this->payment->getPaymentMethodId());
        $this->assertEquals('New description', $this->payment->getDescription());
        $this->assertEquals(2000, $this->payment->getAmountReceived());
        $this->assertEquals(500, $this->payment->getAmountRefunded());
        $this->assertEquals('secret_new', $this->payment->getClientSecret());
        $this->assertEquals('ch_new', $this->payment->getChargeId());
        $this->assertEquals('new@example.com', $this->payment->getReceiptEmail());
        $this->assertEquals('https://example.com/receipt', $this->payment->getReceiptUrl());
        $this->assertEquals('card_declined', $this->payment->getFailureCode());
        $this->assertEquals('Your card was declined', $this->payment->getFailureMessage());
        $this->assertEquals(['key' => 'value'], $this->payment->getMetadata());
        $this->assertEquals(9876543210, $this->payment->getCreatedAt());
    }

    public function testStatusChecks(): void
    {
        $this->assertTrue($this->payment->isSucceeded());
        $this->assertFalse($this->payment->isProcessing());
        $this->assertFalse($this->payment->isCancelled());
        $this->assertFalse($this->payment->requiresAction());
        $this->assertFalse($this->payment->requiresPaymentMethod());

        $this->payment->setStatus(Payment::STATUS_PROCESSING);
        $this->assertTrue($this->payment->isProcessing());

        $this->payment->setStatus(Payment::STATUS_CANCELLED);
        $this->assertTrue($this->payment->isCancelled());

        $this->payment->setStatus(Payment::STATUS_REQUIRES_ACTION);
        $this->assertTrue($this->payment->requiresAction());

        $this->payment->setStatus(Payment::STATUS_REQUIRES_PAYMENT_METHOD);
        $this->assertTrue($this->payment->requiresPaymentMethod());
    }

    public function testHasFailed(): void
    {
        $this->assertFalse($this->payment->hasFailed());

        $this->payment->setFailureCode('card_declined');
        $this->assertTrue($this->payment->hasFailed());

        $this->payment->setFailureCode(null);
        $this->payment->setFailureMessage('Some error');
        $this->assertTrue($this->payment->hasFailed());
    }

    public function testIsRefunded(): void
    {
        $this->assertFalse($this->payment->isRefunded());

        $this->payment->setAmountRefunded(0);
        $this->assertFalse($this->payment->isRefunded());

        $this->payment->setAmountRefunded(500);
        $this->assertTrue($this->payment->isRefunded());
    }

    public function testIsFullyRefunded(): void
    {
        $this->assertFalse($this->payment->isFullyRefunded());

        $this->payment->setAmountRefunded(500);
        $this->assertFalse($this->payment->isFullyRefunded());

        $this->payment->setAmountRefunded(1000);
        $this->assertTrue($this->payment->isFullyRefunded());

        $this->payment->setAmountRefunded(1500);
        $this->assertTrue($this->payment->isFullyRefunded());
    }

    public function testGetNetAmount(): void
    {
        $this->assertEquals(1000, $this->payment->getNetAmount());

        $this->payment->setAmountRefunded(300);
        $this->assertEquals(700, $this->payment->getNetAmount());

        $this->payment->setAmountRefunded(1000);
        $this->assertEquals(0, $this->payment->getNetAmount());
    }

    public function testGetAmountDecimal(): void
    {
        $this->assertEquals(10.00, $this->payment->getAmountDecimal());

        $this->payment->setAmount(1550);
        $this->assertEquals(15.50, $this->payment->getAmountDecimal());

        $this->payment->setAmount(999);
        $this->assertEquals(9.99, $this->payment->getAmountDecimal());
    }

    public function testToArray(): void
    {
        $this->payment->setCustomerId('cus_test');
        $this->payment->setPaymentMethodId('pm_test');
        $this->payment->setDescription('Test payment');
        $this->payment->setMetadata(['test' => true]);

        $array = $this->payment->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('pi_123', $array['id']);
        $this->assertEquals(1000, $array['amount']);
        $this->assertEquals('USD', $array['currency']);
        $this->assertEquals(Payment::STATUS_SUCCEEDED, $array['status']);
        $this->assertEquals('cus_test', $array['customerId']);
        $this->assertEquals('pm_test', $array['paymentMethodId']);
        $this->assertEquals('Test payment', $array['description']);
        $this->assertEquals(['test' => true], $array['metadata']);
    }

    public function testFromArray(): void
    {
        $data = [
            'id' => 'pi_array',
            'amount' => 2500,
            'currency' => 'eur',
            'status' => Payment::STATUS_PROCESSING,
            'customerId' => 'cus_array',
            'paymentMethodId' => 'pm_array',
            'description' => 'Array payment',
            'amountReceived' => 2000,
            'amountRefunded' => 500,
            'metadata' => ['source' => 'test'],
            'createdAt' => 1234567890,
        ];

        $payment = Payment::fromArray($data);

        $this->assertEquals('pi_array', $payment->getId());
        $this->assertEquals(2500, $payment->getAmount());
        $this->assertEquals('EUR', $payment->getCurrency());
        $this->assertEquals(Payment::STATUS_PROCESSING, $payment->getStatus());
        $this->assertEquals('cus_array', $payment->getCustomerId());
        $this->assertEquals('pm_array', $payment->getPaymentMethodId());
        $this->assertEquals('Array payment', $payment->getDescription());
        $this->assertEquals(2000, $payment->getAmountReceived());
        $this->assertEquals(500, $payment->getAmountRefunded());
    }

    public function testFromArrayWithStripeFormat(): void
    {
        $data = [
            'id' => 'pi_stripe',
            'amount' => 3000,
            'currency' => 'usd',
            'status' => 'succeeded',
            'customer' => 'cus_stripe',
            'payment_method' => 'pm_stripe',
            'amount_received' => 3000,
            'amount_refunded' => 0,
            'client_secret' => 'pi_stripe_secret_xxx',
            'receipt_email' => 'stripe@example.com',
            'latest_charge' => [
                'id' => 'ch_stripe',
                'receipt_url' => 'https://receipt.stripe.com/xxx',
            ],
            'last_payment_error' => [
                'code' => 'card_declined',
                'message' => 'Your card was declined',
            ],
            'created' => 1234567890,
        ];

        $payment = Payment::fromArray($data);

        $this->assertEquals('pi_stripe', $payment->getId());
        $this->assertEquals('cus_stripe', $payment->getCustomerId());
        $this->assertEquals('pm_stripe', $payment->getPaymentMethodId());
        $this->assertEquals('pi_stripe_secret_xxx', $payment->getClientSecret());
        $this->assertEquals('stripe@example.com', $payment->getReceiptEmail());
        $this->assertEquals('ch_stripe', $payment->getChargeId());
        $this->assertEquals('https://receipt.stripe.com/xxx', $payment->getReceiptUrl());
        $this->assertEquals('card_declined', $payment->getFailureCode());
        $this->assertEquals('Your card was declined', $payment->getFailureMessage());
        $this->assertEquals(1234567890, $payment->getCreatedAt());
    }

    public function testStatusConstants(): void
    {
        $this->assertEquals('requires_payment_method', Payment::STATUS_REQUIRES_PAYMENT_METHOD);
        $this->assertEquals('requires_confirmation', Payment::STATUS_REQUIRES_CONFIRMATION);
        $this->assertEquals('requires_action', Payment::STATUS_REQUIRES_ACTION);
        $this->assertEquals('processing', Payment::STATUS_PROCESSING);
        $this->assertEquals('requires_capture', Payment::STATUS_REQUIRES_CAPTURE);
        $this->assertEquals('canceled', Payment::STATUS_CANCELLED);
        $this->assertEquals('succeeded', Payment::STATUS_SUCCEEDED);
    }

    public function testFluentInterface(): void
    {
        $result = $this->payment
            ->setId('pi_fluent')
            ->setAmount(5000)
            ->setCurrency('CAD')
            ->setStatus(Payment::STATUS_PROCESSING);

        $this->assertSame($this->payment, $result);
        $this->assertEquals('pi_fluent', $this->payment->getId());
    }
}
