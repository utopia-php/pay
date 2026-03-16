<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Refund\Refund;

class RefundTest extends TestCase
{
    private Refund $refund;

    protected function setUp(): void
    {
        $this->refund = new Refund(
            're_123',
            500, // $5.00
            'USD',
            Refund::STATUS_SUCCEEDED
        );
    }

    public function testConstructor(): void
    {
        $this->assertEquals('re_123', $this->refund->getId());
        $this->assertEquals(500, $this->refund->getAmount());
        $this->assertEquals('USD', $this->refund->getCurrency());
        $this->assertEquals(Refund::STATUS_SUCCEEDED, $this->refund->getStatus());
        $this->assertNotNull($this->refund->getCreatedAt());
    }

    public function testConstructorWithAllParameters(): void
    {
        $refund = new Refund(
            're_full',
            1000,
            'EUR',
            Refund::STATUS_PENDING,
            'pi_123',
            'ch_123',
            Refund::REASON_REQUESTED_BY_CUSTOMER,
            null,
            'RN123456',
            ['order_id' => 'order_123'],
            1234567890
        );

        $this->assertEquals('re_full', $refund->getId());
        $this->assertEquals(1000, $refund->getAmount());
        $this->assertEquals('EUR', $refund->getCurrency());
        $this->assertEquals(Refund::STATUS_PENDING, $refund->getStatus());
        $this->assertEquals('pi_123', $refund->getPaymentId());
        $this->assertEquals('ch_123', $refund->getChargeId());
        $this->assertEquals(Refund::REASON_REQUESTED_BY_CUSTOMER, $refund->getReason());
        $this->assertNull($refund->getFailureReason());
        $this->assertEquals('RN123456', $refund->getReceiptNumber());
        $this->assertEquals(['order_id' => 'order_123'], $refund->getMetadata());
        $this->assertEquals(1234567890, $refund->getCreatedAt());
    }

    public function testGettersAndSetters(): void
    {
        $this->refund->setId('re_new');
        $this->refund->setAmount(750);
        $this->refund->setCurrency('GBP');
        $this->refund->setStatus(Refund::STATUS_PENDING);
        $this->refund->setPaymentId('pi_new');
        $this->refund->setChargeId('ch_new');
        $this->refund->setReason(Refund::REASON_DUPLICATE);
        $this->refund->setFailureReason('expired_or_canceled_card');
        $this->refund->setReceiptNumber('RN999');
        $this->refund->setMetadata(['key' => 'value']);
        $this->refund->setCreatedAt(9876543210);

        $this->assertEquals('re_new', $this->refund->getId());
        $this->assertEquals(750, $this->refund->getAmount());
        $this->assertEquals('GBP', $this->refund->getCurrency());
        $this->assertEquals(Refund::STATUS_PENDING, $this->refund->getStatus());
        $this->assertEquals('pi_new', $this->refund->getPaymentId());
        $this->assertEquals('ch_new', $this->refund->getChargeId());
        $this->assertEquals(Refund::REASON_DUPLICATE, $this->refund->getReason());
        $this->assertEquals('expired_or_canceled_card', $this->refund->getFailureReason());
        $this->assertEquals('RN999', $this->refund->getReceiptNumber());
        $this->assertEquals(['key' => 'value'], $this->refund->getMetadata());
        $this->assertEquals(9876543210, $this->refund->getCreatedAt());
    }

    public function testStatusChecks(): void
    {
        $this->assertTrue($this->refund->isSucceeded());
        $this->assertFalse($this->refund->isPending());
        $this->assertFalse($this->refund->isFailed());
        $this->assertFalse($this->refund->isCancelled());

        $this->refund->setStatus(Refund::STATUS_PENDING);
        $this->assertTrue($this->refund->isPending());

        $this->refund->setStatus(Refund::STATUS_FAILED);
        $this->assertTrue($this->refund->isFailed());

        $this->refund->setStatus(Refund::STATUS_CANCELLED);
        $this->assertTrue($this->refund->isCancelled());
    }

    public function testGetAmountDecimal(): void
    {
        $this->assertEquals(5.00, $this->refund->getAmountDecimal());

        $this->refund->setAmount(1550);
        $this->assertEquals(15.50, $this->refund->getAmountDecimal());

        $this->refund->setAmount(999);
        $this->assertEquals(9.99, $this->refund->getAmountDecimal());
    }

    public function testToArray(): void
    {
        $this->refund->setPaymentId('pi_test');
        $this->refund->setChargeId('ch_test');
        $this->refund->setReason(Refund::REASON_FRAUDULENT);
        $this->refund->setMetadata(['test' => true]);

        $array = $this->refund->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('re_123', $array['id']);
        $this->assertEquals(500, $array['amount']);
        $this->assertEquals('USD', $array['currency']);
        $this->assertEquals(Refund::STATUS_SUCCEEDED, $array['status']);
        $this->assertEquals('pi_test', $array['paymentId']);
        $this->assertEquals('ch_test', $array['chargeId']);
        $this->assertEquals(Refund::REASON_FRAUDULENT, $array['reason']);
        $this->assertEquals(['test' => true], $array['metadata']);
    }

    public function testFromArray(): void
    {
        $data = [
            'id' => 're_array',
            'amount' => 1500,
            'currency' => 'eur',
            'status' => Refund::STATUS_PENDING,
            'paymentId' => 'pi_array',
            'chargeId' => 'ch_array',
            'reason' => Refund::REASON_DUPLICATE,
            'failureReason' => null,
            'receiptNumber' => 'RN_ARRAY',
            'metadata' => ['source' => 'test'],
            'createdAt' => 1234567890,
        ];

        $refund = Refund::fromArray($data);

        $this->assertEquals('re_array', $refund->getId());
        $this->assertEquals(1500, $refund->getAmount());
        $this->assertEquals('EUR', $refund->getCurrency());
        $this->assertEquals(Refund::STATUS_PENDING, $refund->getStatus());
        $this->assertEquals('pi_array', $refund->getPaymentId());
        $this->assertEquals('ch_array', $refund->getChargeId());
        $this->assertEquals(Refund::REASON_DUPLICATE, $refund->getReason());
        $this->assertEquals('RN_ARRAY', $refund->getReceiptNumber());
    }

    public function testFromArrayWithStripeFormat(): void
    {
        $data = [
            'id' => 're_stripe',
            'amount' => 2000,
            'currency' => 'usd',
            'status' => 'succeeded',
            'payment_intent' => 'pi_stripe',
            'charge' => 'ch_stripe',
            'reason' => 'requested_by_customer',
            'failure_reason' => null,
            'receipt_number' => 'RN_STRIPE',
            'created' => 1234567890,
        ];

        $refund = Refund::fromArray($data);

        $this->assertEquals('re_stripe', $refund->getId());
        $this->assertEquals('pi_stripe', $refund->getPaymentId());
        $this->assertEquals('ch_stripe', $refund->getChargeId());
        $this->assertEquals('requested_by_customer', $refund->getReason());
        $this->assertEquals('RN_STRIPE', $refund->getReceiptNumber());
        $this->assertEquals(1234567890, $refund->getCreatedAt());
    }

    public function testStatusConstants(): void
    {
        $this->assertEquals('pending', Refund::STATUS_PENDING);
        $this->assertEquals('succeeded', Refund::STATUS_SUCCEEDED);
        $this->assertEquals('failed', Refund::STATUS_FAILED);
        $this->assertEquals('canceled', Refund::STATUS_CANCELLED);
        $this->assertEquals('requires_action', Refund::STATUS_REQUIRES_ACTION);
    }

    public function testReasonConstants(): void
    {
        $this->assertEquals('duplicate', Refund::REASON_DUPLICATE);
        $this->assertEquals('fraudulent', Refund::REASON_FRAUDULENT);
        $this->assertEquals('requested_by_customer', Refund::REASON_REQUESTED_BY_CUSTOMER);
    }

    public function testFluentInterface(): void
    {
        $result = $this->refund
            ->setId('re_fluent')
            ->setAmount(2500)
            ->setCurrency('CAD')
            ->setStatus(Refund::STATUS_PENDING);

        $this->assertSame($this->refund, $result);
        $this->assertEquals('re_fluent', $this->refund->getId());
    }
}
