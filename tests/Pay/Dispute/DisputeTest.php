<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Dispute\Dispute;

class DisputeTest extends TestCase
{
    public function testFromArray(): void
    {
        $dispute = Dispute::fromArray([
            'id' => 'dp_1Q0abc',
            'object' => 'dispute',
            'amount' => 2500,
            'charge' => 'ch_3Q0abc',
            'created' => 1726000000,
            'currency' => 'usd',
            'evidence_details' => ['due_by' => 1727000000, 'has_evidence' => false, 'past_due' => false, 'submission_count' => 0],
            'metadata' => [],
            'payment_intent' => 'pi_3Q0abc',
            'reason' => 'fraudulent',
            'status' => 'needs_response',
        ]);

        $this->assertEquals('dp_1Q0abc', $dispute->getId());
        $this->assertEquals(2500, $dispute->getAmount());
        $this->assertEquals('usd', $dispute->getCurrency());
        $this->assertEquals('fraudulent', $dispute->getReason());
        $this->assertEquals('needs_response', $dispute->getStatus());
        $this->assertEquals('ch_3Q0abc', $dispute->getChargeId());
        $this->assertEquals('pi_3Q0abc', $dispute->getPaymentIntentId());
        $this->assertNull($dispute->getPaymentIntentMetadata());
        $this->assertEquals([], $dispute->getMetadata());
        $this->assertEquals(1727000000, $dispute->getEvidenceDueBy());
        $this->assertEquals(1726000000, $dispute->getCreatedAt());
    }

    public function testFromArrayWithExpandedObjects(): void
    {
        $dispute = Dispute::fromArray([
            'id' => 'dp_123',
            'charge' => ['id' => 'ch_123', 'object' => 'charge'],
            'payment_intent' => ['id' => 'pi_123', 'object' => 'payment_intent', 'metadata' => ['invoiceId' => 'inv_1', 'teamId' => 'team_1']],
        ]);

        $this->assertEquals('ch_123', $dispute->getChargeId());
        $this->assertEquals('pi_123', $dispute->getPaymentIntentId());
        $this->assertEquals(['invoiceId' => 'inv_1', 'teamId' => 'team_1'], $dispute->getPaymentIntentMetadata());
    }

    public function testFromArrayWithoutOptionalFields(): void
    {
        $dispute = Dispute::fromArray(['id' => 'dp_123', 'payment_intent' => null]);

        $this->assertNull($dispute->getPaymentIntentId());
        $this->assertNull($dispute->getReason());
        $this->assertNull($dispute->getAmount());
        $this->assertNull($dispute->getEvidenceDueBy());
    }
}
