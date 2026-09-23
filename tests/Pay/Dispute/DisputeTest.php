<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Dispute\Dispute;

class DisputeTest extends TestCase
{
    public function testFromArray(): void
    {
        $dispute = Dispute::fromArray([
            'id' => 'dp_123',
            'object' => 'dispute',
            'amount' => 2500,
            'currency' => 'usd',
            'reason' => 'fraudulent',
            'status' => 'needs_response',
            'charge' => 'ch_123',
            'payment_intent' => 'pi_123',
            'metadata' => ['invoiceId' => 'inv_1', 'teamId' => 'team_1'],
            'evidence_details' => ['due_by' => 1700000000, 'has_evidence' => false],
        ]);

        $this->assertEquals('dp_123', $dispute->getId());
        $this->assertEquals(2500, $dispute->getAmount());
        $this->assertEquals('usd', $dispute->getCurrency());
        $this->assertEquals('fraudulent', $dispute->getReason());
        $this->assertEquals('needs_response', $dispute->getStatus());
        $this->assertEquals('ch_123', $dispute->getChargeId());
        $this->assertEquals('pi_123', $dispute->getPaymentIntentId());
        $this->assertEquals(['invoiceId' => 'inv_1', 'teamId' => 'team_1'], $dispute->getMetadata());
        $this->assertEquals(1700000000, $dispute->getEvidenceDueBy());
    }

    public function testFromArrayWithExpandedObjects(): void
    {
        $dispute = Dispute::fromArray([
            'id' => 'dp_123',
            'charge' => ['id' => 'ch_123', 'object' => 'charge'],
            'payment_intent' => ['id' => 'pi_123', 'object' => 'payment_intent'],
            'evidence_details' => ['due_by' => null],
        ]);

        $this->assertEquals('ch_123', $dispute->getChargeId());
        $this->assertEquals('pi_123', $dispute->getPaymentIntentId());
        $this->assertNull($dispute->getEvidenceDueBy());
        $this->assertEquals([], $dispute->getMetadata());
    }
}
