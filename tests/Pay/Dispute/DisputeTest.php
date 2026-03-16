<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Dispute\Dispute;

class DisputeTest extends TestCase
{
    private Dispute $dispute;

    protected function setUp(): void
    {
        $this->dispute = new Dispute(
            'dp_123',
            5000,
            'USD',
            Dispute::STATUS_NEEDS_RESPONSE,
            'ch_123',
            'pi_123',
            Dispute::REASON_FRAUDULENT
        );
    }

    public function testConstructor(): void
    {
        $this->assertEquals('dp_123', $this->dispute->getId());
        $this->assertEquals(5000, $this->dispute->getAmount());
        $this->assertEquals('USD', $this->dispute->getCurrency());
        $this->assertEquals(Dispute::STATUS_NEEDS_RESPONSE, $this->dispute->getStatus());
        $this->assertEquals('ch_123', $this->dispute->getChargeId());
        $this->assertEquals('pi_123', $this->dispute->getPaymentIntentId());
        $this->assertEquals(Dispute::REASON_FRAUDULENT, $this->dispute->getReason());
        $this->assertNotNull($this->dispute->getCreatedAt());
    }

    public function testConstructorDefaults(): void
    {
        $dispute = new Dispute('dp_min', 1000, 'EUR');

        $this->assertEquals(Dispute::STATUS_NEEDS_RESPONSE, $dispute->getStatus());
        $this->assertNull($dispute->getChargeId());
        $this->assertNull($dispute->getPaymentIntentId());
        $this->assertNull($dispute->getReason());
        $this->assertFalse($dispute->isChargeRefundable());
        $this->assertNull($dispute->getEvidenceDueBy());
        $this->assertFalse($dispute->hasEvidence());
        $this->assertFalse($dispute->isPastDue());
        $this->assertNull($dispute->getNetworkReasonCode());
        $this->assertEquals([], $dispute->getMetadata());
    }

    public function testConstructorWithAllParameters(): void
    {
        $dispute = new Dispute(
            'dp_full',
            10000,
            'GBP',
            Dispute::STATUS_UNDER_REVIEW,
            'ch_full',
            'pi_full',
            Dispute::REASON_PRODUCT_NOT_RECEIVED,
            true,
            1700000000,
            true,
            false,
            '4837',
            ['order_id' => 'ord_123'],
            1234567890
        );

        $this->assertEquals('dp_full', $dispute->getId());
        $this->assertEquals(10000, $dispute->getAmount());
        $this->assertEquals('GBP', $dispute->getCurrency());
        $this->assertEquals(Dispute::STATUS_UNDER_REVIEW, $dispute->getStatus());
        $this->assertEquals('ch_full', $dispute->getChargeId());
        $this->assertEquals('pi_full', $dispute->getPaymentIntentId());
        $this->assertEquals(Dispute::REASON_PRODUCT_NOT_RECEIVED, $dispute->getReason());
        $this->assertTrue($dispute->isChargeRefundable());
        $this->assertEquals(1700000000, $dispute->getEvidenceDueBy());
        $this->assertTrue($dispute->hasEvidence());
        $this->assertFalse($dispute->isPastDue());
        $this->assertEquals('4837', $dispute->getNetworkReasonCode());
        $this->assertEquals(['order_id' => 'ord_123'], $dispute->getMetadata());
        $this->assertEquals(1234567890, $dispute->getCreatedAt());
    }

    public function testGettersAndSetters(): void
    {
        $this->dispute->setId('dp_new');
        $this->dispute->setAmount(7500);
        $this->dispute->setCurrency('EUR');
        $this->dispute->setStatus(Dispute::STATUS_WON);
        $this->dispute->setChargeId('ch_new');
        $this->dispute->setPaymentIntentId('pi_new');
        $this->dispute->setReason(Dispute::REASON_DUPLICATE);
        $this->dispute->setIsChargeRefundable(true);
        $this->dispute->setEvidenceDueBy(1700000000);
        $this->dispute->setHasEvidence(true);
        $this->dispute->setPastDue(true);
        $this->dispute->setNetworkReasonCode('4837');
        $this->dispute->setMetadata(['key' => 'value']);
        $this->dispute->setCreatedAt(9876543210);

        $this->assertEquals('dp_new', $this->dispute->getId());
        $this->assertEquals(7500, $this->dispute->getAmount());
        $this->assertEquals('EUR', $this->dispute->getCurrency());
        $this->assertEquals(Dispute::STATUS_WON, $this->dispute->getStatus());
        $this->assertEquals('ch_new', $this->dispute->getChargeId());
        $this->assertEquals('pi_new', $this->dispute->getPaymentIntentId());
        $this->assertEquals(Dispute::REASON_DUPLICATE, $this->dispute->getReason());
        $this->assertTrue($this->dispute->isChargeRefundable());
        $this->assertEquals(1700000000, $this->dispute->getEvidenceDueBy());
        $this->assertTrue($this->dispute->hasEvidence());
        $this->assertTrue($this->dispute->isPastDue());
        $this->assertEquals('4837', $this->dispute->getNetworkReasonCode());
        $this->assertEquals(['key' => 'value'], $this->dispute->getMetadata());
        $this->assertEquals(9876543210, $this->dispute->getCreatedAt());
    }

    public function testStatusChecks(): void
    {
        $this->dispute->setStatus(Dispute::STATUS_WON);
        $this->assertTrue($this->dispute->isWon());
        $this->assertFalse($this->dispute->isLost());

        $this->dispute->setStatus(Dispute::STATUS_LOST);
        $this->assertTrue($this->dispute->isLost());
        $this->assertFalse($this->dispute->isWon());
    }

    public function testNeedsResponse(): void
    {
        $this->dispute->setStatus(Dispute::STATUS_NEEDS_RESPONSE);
        $this->assertTrue($this->dispute->needsResponse());

        $this->dispute->setStatus(Dispute::STATUS_WARNING_NEEDS_RESPONSE);
        $this->assertTrue($this->dispute->needsResponse());

        $this->dispute->setStatus(Dispute::STATUS_UNDER_REVIEW);
        $this->assertFalse($this->dispute->needsResponse());

        $this->dispute->setStatus(Dispute::STATUS_WON);
        $this->assertFalse($this->dispute->needsResponse());
    }

    public function testIsUnderReview(): void
    {
        $this->dispute->setStatus(Dispute::STATUS_UNDER_REVIEW);
        $this->assertTrue($this->dispute->isUnderReview());

        $this->dispute->setStatus(Dispute::STATUS_WARNING_UNDER_REVIEW);
        $this->assertTrue($this->dispute->isUnderReview());

        $this->dispute->setStatus(Dispute::STATUS_NEEDS_RESPONSE);
        $this->assertFalse($this->dispute->isUnderReview());
    }

    public function testIsClosed(): void
    {
        $this->dispute->setStatus(Dispute::STATUS_WON);
        $this->assertTrue($this->dispute->isClosed());

        $this->dispute->setStatus(Dispute::STATUS_LOST);
        $this->assertTrue($this->dispute->isClosed());

        $this->dispute->setStatus(Dispute::STATUS_WARNING_CLOSED);
        $this->assertTrue($this->dispute->isClosed());

        $this->dispute->setStatus(Dispute::STATUS_NEEDS_RESPONSE);
        $this->assertFalse($this->dispute->isClosed());
    }

    public function testIsWarning(): void
    {
        $this->dispute->setStatus(Dispute::STATUS_WARNING_NEEDS_RESPONSE);
        $this->assertTrue($this->dispute->isWarning());

        $this->dispute->setStatus(Dispute::STATUS_WARNING_UNDER_REVIEW);
        $this->assertTrue($this->dispute->isWarning());

        $this->dispute->setStatus(Dispute::STATUS_WARNING_CLOSED);
        $this->assertTrue($this->dispute->isWarning());

        $this->dispute->setStatus(Dispute::STATUS_NEEDS_RESPONSE);
        $this->assertFalse($this->dispute->isWarning());
    }

    public function testGetAmountDecimal(): void
    {
        $this->assertEquals(50.00, $this->dispute->getAmountDecimal());

        $this->dispute->setAmount(1550);
        $this->assertEquals(15.50, $this->dispute->getAmountDecimal());

        $this->dispute->setAmount(999);
        $this->assertEquals(9.99, $this->dispute->getAmountDecimal());
    }

    public function testGetDaysRemaining(): void
    {
        $this->assertNull($this->dispute->getDaysRemaining());

        // Set deadline to 3 days from now
        $this->dispute->setEvidenceDueBy(time() + (3 * 86400));
        $this->assertEquals(3, $this->dispute->getDaysRemaining());

        // Set deadline to past
        $this->dispute->setEvidenceDueBy(time() - 86400);
        $this->assertEquals(0, $this->dispute->getDaysRemaining());
    }

    public function testToArray(): void
    {
        $array = $this->dispute->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('dp_123', $array['id']);
        $this->assertEquals(5000, $array['amount']);
        $this->assertEquals('USD', $array['currency']);
        $this->assertEquals(Dispute::STATUS_NEEDS_RESPONSE, $array['status']);
        $this->assertEquals('ch_123', $array['chargeId']);
        $this->assertEquals('pi_123', $array['paymentIntentId']);
        $this->assertEquals(Dispute::REASON_FRAUDULENT, $array['reason']);
        $this->assertArrayHasKey('isChargeRefundable', $array);
        $this->assertArrayHasKey('evidenceDueBy', $array);
        $this->assertArrayHasKey('hasEvidence', $array);
        $this->assertArrayHasKey('pastDue', $array);
        $this->assertArrayHasKey('createdAt', $array);
    }

    public function testFromArray(): void
    {
        $data = [
            'id' => 'dp_from',
            'amount' => 2500,
            'currency' => 'eur',
            'status' => 'won',
            'chargeId' => 'ch_from',
            'paymentIntentId' => 'pi_from',
            'reason' => 'duplicate',
            'isChargeRefundable' => true,
            'evidenceDueBy' => 1700000000,
            'hasEvidence' => true,
            'pastDue' => false,
            'networkReasonCode' => '4837',
            'metadata' => ['key' => 'val'],
            'createdAt' => 1234567890,
        ];

        $dispute = Dispute::fromArray($data);

        $this->assertEquals('dp_from', $dispute->getId());
        $this->assertEquals(2500, $dispute->getAmount());
        $this->assertEquals('EUR', $dispute->getCurrency());
        $this->assertEquals('won', $dispute->getStatus());
        $this->assertEquals('ch_from', $dispute->getChargeId());
        $this->assertEquals('pi_from', $dispute->getPaymentIntentId());
        $this->assertEquals('duplicate', $dispute->getReason());
        $this->assertTrue($dispute->isChargeRefundable());
        $this->assertEquals(1700000000, $dispute->getEvidenceDueBy());
        $this->assertTrue($dispute->hasEvidence());
        $this->assertFalse($dispute->isPastDue());
        $this->assertEquals('4837', $dispute->getNetworkReasonCode());
        $this->assertEquals(1234567890, $dispute->getCreatedAt());
    }

    public function testFromArrayWithStripeFormat(): void
    {
        $data = [
            'id' => 'dp_stripe',
            'amount' => 3000,
            'currency' => 'usd',
            'status' => 'needs_response',
            'charge' => 'ch_stripe',
            'payment_intent' => 'pi_stripe',
            'reason' => 'fraudulent',
            'is_charge_refundable' => false,
            'evidence_details' => [
                'due_by' => 1700000000,
                'has_evidence' => false,
                'past_due' => true,
            ],
            'network_reason_code' => '10.4',
            'created' => 1234567890,
        ];

        $dispute = Dispute::fromArray($data);

        $this->assertEquals('dp_stripe', $dispute->getId());
        $this->assertEquals('ch_stripe', $dispute->getChargeId());
        $this->assertEquals('pi_stripe', $dispute->getPaymentIntentId());
        $this->assertFalse($dispute->isChargeRefundable());
        $this->assertEquals(1700000000, $dispute->getEvidenceDueBy());
        $this->assertFalse($dispute->hasEvidence());
        $this->assertTrue($dispute->isPastDue());
        $this->assertEquals('10.4', $dispute->getNetworkReasonCode());
        $this->assertEquals(1234567890, $dispute->getCreatedAt());
    }

    public function testStatusConstants(): void
    {
        $this->assertEquals('warning_needs_response', Dispute::STATUS_WARNING_NEEDS_RESPONSE);
        $this->assertEquals('warning_under_review', Dispute::STATUS_WARNING_UNDER_REVIEW);
        $this->assertEquals('warning_closed', Dispute::STATUS_WARNING_CLOSED);
        $this->assertEquals('needs_response', Dispute::STATUS_NEEDS_RESPONSE);
        $this->assertEquals('under_review', Dispute::STATUS_UNDER_REVIEW);
        $this->assertEquals('won', Dispute::STATUS_WON);
        $this->assertEquals('lost', Dispute::STATUS_LOST);
    }

    public function testReasonConstants(): void
    {
        $this->assertEquals('duplicate', Dispute::REASON_DUPLICATE);
        $this->assertEquals('fraudulent', Dispute::REASON_FRAUDULENT);
        $this->assertEquals('subscription_canceled', Dispute::REASON_SUBSCRIPTION_CANCELED);
        $this->assertEquals('product_unacceptable', Dispute::REASON_PRODUCT_UNACCEPTABLE);
        $this->assertEquals('product_not_received', Dispute::REASON_PRODUCT_NOT_RECEIVED);
        $this->assertEquals('unrecognized', Dispute::REASON_UNRECOGNIZED);
        $this->assertEquals('credit_not_processed', Dispute::REASON_CREDIT_NOT_PROCESSED);
        $this->assertEquals('general', Dispute::REASON_GENERAL);
        $this->assertEquals('incorrect_account_details', Dispute::REASON_INCORRECT_ACCOUNT_DETAILS);
        $this->assertEquals('insufficient_funds', Dispute::REASON_INSUFFICIENT_FUNDS);
        $this->assertEquals('bank_cannot_process', Dispute::REASON_BANK_CANNOT_PROCESS);
        $this->assertEquals('debit_not_authorized', Dispute::REASON_DEBIT_NOT_AUTHORIZED);
    }

    public function testFluentInterface(): void
    {
        $result = $this->dispute
            ->setId('dp_fluent')
            ->setAmount(8000)
            ->setCurrency('CAD')
            ->setStatus(Dispute::STATUS_WON);

        $this->assertSame($this->dispute, $result);
        $this->assertEquals('dp_fluent', $this->dispute->getId());
    }
}
