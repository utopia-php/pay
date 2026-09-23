<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Payment\Payment;

class PaymentTest extends TestCase
{
    public function testFromArray(): void
    {
        $payment = Payment::fromArray([
            'id' => 'pi_123',
            'object' => 'payment_intent',
            'amount' => 2500,
            'amount_received' => 2500,
            'currency' => 'usd',
            'status' => 'succeeded',
            'customer' => 'cus_123',
            'payment_method' => 'pm_123',
            'latest_charge' => 'ch_123',
            'client_secret' => 'pi_123_secret_abc',
            'metadata' => ['invoiceId' => 'inv_1'],
            'created' => 1700000000,
        ]);

        $this->assertEquals('pi_123', $payment->getId());
        $this->assertEquals(2500, $payment->getAmount());
        $this->assertEquals(2500, $payment->getAmountReceived());
        $this->assertEquals('usd', $payment->getCurrency());
        $this->assertEquals('cus_123', $payment->getCustomerId());
        $this->assertEquals('pm_123', $payment->getPaymentMethodId());
        $this->assertEquals('ch_123', $payment->getChargeId());
        $this->assertEquals('pi_123_secret_abc', $payment->getClientSecret());
        $this->assertEquals(['invoiceId' => 'inv_1'], $payment->getMetadata());
        $this->assertEquals(1700000000, $payment->getCreatedAt());
        $this->assertTrue($payment->isSucceeded());
        $this->assertNull($payment->getErrorCode());
    }

    public function testFromArrayWithExpandedObjects(): void
    {
        $payment = Payment::fromArray([
            'id' => 'pi_123',
            'amount' => 1000,
            'currency' => 'usd',
            'status' => 'requires_payment_method',
            'customer' => ['id' => 'cus_123', 'object' => 'customer'],
            'payment_method' => ['id' => 'pm_123', 'object' => 'payment_method'],
            'latest_charge' => ['id' => 'ch_123', 'object' => 'charge'],
        ]);

        $this->assertEquals('cus_123', $payment->getCustomerId());
        $this->assertEquals('pm_123', $payment->getPaymentMethodId());
        $this->assertEquals('ch_123', $payment->getChargeId());
        $this->assertEquals([], $payment->getMetadata());
        $this->assertNull($payment->getCreatedAt());
    }

    public function testFromArrayLastPaymentError(): void
    {
        $payment = Payment::fromArray([
            'id' => 'pi_123',
            'amount' => 1000,
            'currency' => 'usd',
            'status' => 'requires_payment_method',
            'last_payment_error' => [
                'type' => 'card_error',
                'code' => 'card_declined',
                'decline_code' => 'insufficient_funds',
                'message' => 'Your card has insufficient funds.',
            ],
        ]);

        $this->assertTrue($payment->requiresPaymentMethod());
        $this->assertEquals('insufficient_funds', $payment->getErrorCode());
        $this->assertEquals('Your card has insufficient funds.', $payment->getErrorMessage());
    }

    public function testStatusChecks(): void
    {
        $checks = [
            Payment::STATUS_SUCCEEDED => 'isSucceeded',
            Payment::STATUS_PROCESSING => 'isProcessing',
            Payment::STATUS_CANCELED => 'isCanceled',
            Payment::STATUS_REQUIRES_ACTION => 'requiresAction',
            Payment::STATUS_REQUIRES_CAPTURE => 'requiresCapture',
            Payment::STATUS_REQUIRES_PAYMENT_METHOD => 'requiresPaymentMethod',
        ];

        foreach ($checks as $status => $method) {
            $payment = new Payment('pi_123', 1000, 'usd', $status);
            foreach ($checks as $other) {
                $this->assertSame($other === $method, $payment->$other(), $status.' '.$other);
            }
        }
    }
}
