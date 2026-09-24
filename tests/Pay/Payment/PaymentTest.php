<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Payment\Payment;

class PaymentTest extends TestCase
{
    public function testFromArray(): void
    {
        $payment = Payment::fromArray([
            'id' => 'pi_3Q0abc',
            'object' => 'payment_intent',
            'amount' => 2500,
            'amount_received' => 2500,
            'capture_method' => 'automatic',
            'client_secret' => 'pi_3Q0abc_secret_xyz',
            'created' => 1726000000,
            'currency' => 'usd',
            'customer' => 'cus_Qabc',
            'last_payment_error' => null,
            'latest_charge' => 'ch_3Q0abc',
            'metadata' => ['invoiceId' => 'inv_1', 'teamId' => 'team_1'],
            'next_action' => null,
            'payment_method' => 'pm_1Q0abc',
            'status' => 'succeeded',
        ]);

        $this->assertEquals('pi_3Q0abc', $payment->getId());
        $this->assertEquals(2500, $payment->getAmount());
        $this->assertEquals(2500, $payment->getAmountReceived());
        $this->assertEquals('usd', $payment->getCurrency());
        $this->assertEquals('succeeded', $payment->getStatus());
        $this->assertEquals('cus_Qabc', $payment->getCustomerId());
        $this->assertEquals('pm_1Q0abc', $payment->getPaymentMethodId());
        $this->assertEquals('ch_3Q0abc', $payment->getLatestChargeId());
        $this->assertEquals('pi_3Q0abc_secret_xyz', $payment->getClientSecret());
        $this->assertEquals(['invoiceId' => 'inv_1', 'teamId' => 'team_1'], $payment->getMetadata());
        $this->assertEquals(1726000000, $payment->getCreatedAt());
        $this->assertNull($payment->getErrorCode());
        $this->assertNull($payment->getNextAction());
        $this->assertEquals([], $payment->getCharges());
        $this->assertEquals('automatic', $payment->getRaw()['capture_method']);
        $this->assertTrue($payment->isSucceeded());
        $this->assertFalse($payment->requiresAction());
    }

    public function testFromArrayWithExpandedObjects(): void
    {
        $payment = Payment::fromArray([
            'id' => 'pi_123',
            'status' => 'requires_capture',
            'customer' => ['id' => 'cus_123', 'object' => 'customer'],
            'payment_method' => ['id' => 'pm_123', 'object' => 'payment_method'],
            'latest_charge' => ['id' => 'ch_123', 'object' => 'charge'],
        ]);

        $this->assertEquals('cus_123', $payment->getCustomerId());
        $this->assertEquals('pm_123', $payment->getPaymentMethodId());
        $this->assertEquals('ch_123', $payment->getLatestChargeId());
        $this->assertTrue($payment->requiresCapture());
    }

    public function testFromArrayWithFailedAttempt(): void
    {
        $payment = Payment::fromArray([
            'id' => 'pi_123',
            'object' => 'payment_intent',
            'amount' => 5000,
            'status' => 'requires_payment_method',
            'last_payment_error' => [
                'code' => 'card_declined',
                'decline_code' => 'insufficient_funds',
                'message' => 'Your card has insufficient funds.',
                'type' => 'card_error',
            ],
        ]);

        $this->assertEquals('card_declined', $payment->getErrorCode());
        $this->assertEquals('insufficient_funds', $payment->getDeclineCode());
        $this->assertEquals('Your card has insufficient funds.', $payment->getErrorMessage());
        $this->assertTrue($payment->requiresPaymentMethod());
    }

    public function testFromArrayRequiringAction(): void
    {
        $payment = Payment::fromArray([
            'id' => 'pi_123',
            'status' => 'requires_action',
            'next_action' => [
                'type' => 'use_stripe_sdk',
                'use_stripe_sdk' => ['type' => 'three_d_secure_redirect', 'stripe_js' => 'https://hooks.stripe.com/3d_secure_2/hosted'],
            ],
        ]);

        $this->assertTrue($payment->requiresAction());
        $this->assertEquals('https://hooks.stripe.com/3d_secure_2/hosted', $payment->getNextAction()['use_stripe_sdk']['stripe_js'] ?? null);
    }

    public function testFromArrayWithLegacyCharges(): void
    {
        $payment = Payment::fromArray([
            'id' => 'pi_123',
            'status' => 'succeeded',
            'charges' => [
                'object' => 'list',
                'data' => [
                    ['id' => 'ch_1', 'object' => 'charge', 'amount' => 2000, 'amount_refunded' => 500, 'refunded' => false, 'status' => 'succeeded'],
                    ['id' => 'ch_2', 'object' => 'charge', 'amount' => 1000, 'amount_refunded' => 1000, 'refunded' => true, 'status' => 'succeeded'],
                ],
                'has_more' => false,
            ],
        ]);

        $charges = $payment->getCharges();
        $this->assertCount(2, $charges);
        $this->assertEquals('ch_1', $charges[0]->getId());
        $this->assertEquals(500, $charges[0]->getAmountRefunded());
        $this->assertFalse($charges[0]->isRefunded());
        $this->assertTrue($charges[1]->isRefunded());
    }

    /**
     * Missing fields stay null so callers keep their own fallbacks
     */
    public function testFromArrayEmpty(): void
    {
        $payment = Payment::fromArray([]);

        $this->assertNull($payment->getId());
        $this->assertNull($payment->getStatus());
        $this->assertNull($payment->getAmount());
        $this->assertNull($payment->getCurrency());
        $this->assertNull($payment->getClientSecret());
        $this->assertNull($payment->getErrorMessage());
        $this->assertEquals([], $payment->getMetadata());
        $this->assertEquals([], $payment->getRaw());
        $this->assertFalse($payment->isSucceeded());
    }
}
