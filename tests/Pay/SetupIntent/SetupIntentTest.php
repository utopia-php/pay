<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\SetupIntent\SetupIntent;

class SetupIntentTest extends TestCase
{
    private SetupIntent $setupIntent;

    protected function setUp(): void
    {
        $this->setupIntent = new SetupIntent(
            'seti_123',
            SetupIntent::STATUS_SUCCEEDED,
            'cus_123',
            'pm_123'
        );
    }

    public function testConstructor(): void
    {
        $this->assertEquals('seti_123', $this->setupIntent->getId());
        $this->assertEquals(SetupIntent::STATUS_SUCCEEDED, $this->setupIntent->getStatus());
        $this->assertEquals('cus_123', $this->setupIntent->getCustomerId());
        $this->assertEquals('pm_123', $this->setupIntent->getPaymentMethodId());
        $this->assertNotNull($this->setupIntent->getCreatedAt());
    }

    public function testConstructorDefaults(): void
    {
        $si = new SetupIntent('seti_default');

        $this->assertEquals('seti_default', $si->getId());
        $this->assertEquals(SetupIntent::STATUS_REQUIRES_PAYMENT_METHOD, $si->getStatus());
        $this->assertNull($si->getCustomerId());
        $this->assertNull($si->getPaymentMethodId());
        $this->assertNull($si->getClientSecret());
        $this->assertEquals(SetupIntent::USAGE_OFF_SESSION, $si->getUsage());
        $this->assertNull($si->getDescription());
        $this->assertNull($si->getMandateId());
        $this->assertEquals(['card'], $si->getPaymentMethodTypes());
        $this->assertNull($si->getCancellationReason());
        $this->assertEquals([], $si->getLastSetupError());
        $this->assertEquals([], $si->getNextAction());
        $this->assertEquals([], $si->getMetadata());
        $this->assertNotNull($si->getCreatedAt());
    }

    public function testConstructorWithAllParameters(): void
    {
        $si = new SetupIntent(
            'seti_full',
            SetupIntent::STATUS_CANCELED,
            'cus_full',
            'pm_full',
            'seti_full_secret_xxx',
            SetupIntent::USAGE_ON_SESSION,
            'Test setup',
            'mandate_123',
            ['card', 'sepa_debit'],
            SetupIntent::CANCELLATION_ABANDONED,
            ['code' => 'card_declined', 'message' => 'Card declined'],
            ['type' => 'redirect_to_url'],
            ['order_id' => 'ord_123'],
            1234567890
        );

        $this->assertEquals('seti_full', $si->getId());
        $this->assertEquals(SetupIntent::STATUS_CANCELED, $si->getStatus());
        $this->assertEquals('cus_full', $si->getCustomerId());
        $this->assertEquals('pm_full', $si->getPaymentMethodId());
        $this->assertEquals('seti_full_secret_xxx', $si->getClientSecret());
        $this->assertEquals(SetupIntent::USAGE_ON_SESSION, $si->getUsage());
        $this->assertEquals('Test setup', $si->getDescription());
        $this->assertEquals('mandate_123', $si->getMandateId());
        $this->assertEquals(['card', 'sepa_debit'], $si->getPaymentMethodTypes());
        $this->assertEquals(SetupIntent::CANCELLATION_ABANDONED, $si->getCancellationReason());
        $this->assertEquals('Card declined', $si->getLastSetupError()['message']);
        $this->assertEquals(['type' => 'redirect_to_url'], $si->getNextAction());
        $this->assertEquals(['order_id' => 'ord_123'], $si->getMetadata());
        $this->assertEquals(1234567890, $si->getCreatedAt());
    }

    public function testGettersAndSetters(): void
    {
        $this->setupIntent->setId('seti_new');
        $this->setupIntent->setStatus(SetupIntent::STATUS_REQUIRES_ACTION);
        $this->setupIntent->setCustomerId('cus_new');
        $this->setupIntent->setPaymentMethodId('pm_new');
        $this->setupIntent->setClientSecret('secret_new');
        $this->setupIntent->setUsage(SetupIntent::USAGE_ON_SESSION);
        $this->setupIntent->setDescription('New description');
        $this->setupIntent->setMandateId('mandate_new');
        $this->setupIntent->setPaymentMethodTypes(['card', 'ideal']);
        $this->setupIntent->setCancellationReason(SetupIntent::CANCELLATION_DUPLICATE);
        $this->setupIntent->setLastSetupError(['code' => 'error']);
        $this->setupIntent->setNextAction(['type' => 'use_stripe_sdk']);
        $this->setupIntent->setMetadata(['key' => 'value']);
        $this->setupIntent->setCreatedAt(9876543210);

        $this->assertEquals('seti_new', $this->setupIntent->getId());
        $this->assertEquals(SetupIntent::STATUS_REQUIRES_ACTION, $this->setupIntent->getStatus());
        $this->assertEquals('cus_new', $this->setupIntent->getCustomerId());
        $this->assertEquals('pm_new', $this->setupIntent->getPaymentMethodId());
        $this->assertEquals('secret_new', $this->setupIntent->getClientSecret());
        $this->assertEquals(SetupIntent::USAGE_ON_SESSION, $this->setupIntent->getUsage());
        $this->assertEquals('New description', $this->setupIntent->getDescription());
        $this->assertEquals('mandate_new', $this->setupIntent->getMandateId());
        $this->assertEquals(['card', 'ideal'], $this->setupIntent->getPaymentMethodTypes());
        $this->assertEquals(SetupIntent::CANCELLATION_DUPLICATE, $this->setupIntent->getCancellationReason());
        $this->assertEquals(['code' => 'error'], $this->setupIntent->getLastSetupError());
        $this->assertEquals(['type' => 'use_stripe_sdk'], $this->setupIntent->getNextAction());
        $this->assertEquals(['key' => 'value'], $this->setupIntent->getMetadata());
        $this->assertEquals(9876543210, $this->setupIntent->getCreatedAt());
    }

    public function testStatusChecks(): void
    {
        $this->assertTrue($this->setupIntent->isSucceeded());
        $this->assertFalse($this->setupIntent->isCanceled());
        $this->assertFalse($this->setupIntent->requiresAction());
        $this->assertFalse($this->setupIntent->requiresPaymentMethod());
        $this->assertFalse($this->setupIntent->requiresConfirmation());
        $this->assertFalse($this->setupIntent->isProcessing());

        $this->setupIntent->setStatus(SetupIntent::STATUS_CANCELED);
        $this->assertTrue($this->setupIntent->isCanceled());

        $this->setupIntent->setStatus(SetupIntent::STATUS_REQUIRES_ACTION);
        $this->assertTrue($this->setupIntent->requiresAction());

        $this->setupIntent->setStatus(SetupIntent::STATUS_REQUIRES_PAYMENT_METHOD);
        $this->assertTrue($this->setupIntent->requiresPaymentMethod());

        $this->setupIntent->setStatus(SetupIntent::STATUS_REQUIRES_CONFIRMATION);
        $this->assertTrue($this->setupIntent->requiresConfirmation());

        $this->setupIntent->setStatus(SetupIntent::STATUS_PROCESSING);
        $this->assertTrue($this->setupIntent->isProcessing());
    }

    public function testIsComplete(): void
    {
        $this->setupIntent->setStatus(SetupIntent::STATUS_SUCCEEDED);
        $this->assertTrue($this->setupIntent->isComplete());

        $this->setupIntent->setStatus(SetupIntent::STATUS_CANCELED);
        $this->assertTrue($this->setupIntent->isComplete());

        $this->setupIntent->setStatus(SetupIntent::STATUS_PROCESSING);
        $this->assertFalse($this->setupIntent->isComplete());

        $this->setupIntent->setStatus(SetupIntent::STATUS_REQUIRES_ACTION);
        $this->assertFalse($this->setupIntent->isComplete());
    }

    public function testIsOffSession(): void
    {
        $this->assertTrue($this->setupIntent->isOffSession());

        $this->setupIntent->setUsage(SetupIntent::USAGE_ON_SESSION);
        $this->assertFalse($this->setupIntent->isOffSession());
    }

    public function testErrorMethods(): void
    {
        $this->assertFalse($this->setupIntent->hasError());
        $this->assertNull($this->setupIntent->getErrorMessage());
        $this->assertNull($this->setupIntent->getErrorCode());

        $this->setupIntent->setLastSetupError([
            'code' => 'card_declined',
            'message' => 'Your card was declined',
        ]);

        $this->assertTrue($this->setupIntent->hasError());
        $this->assertEquals('Your card was declined', $this->setupIntent->getErrorMessage());
        $this->assertEquals('card_declined', $this->setupIntent->getErrorCode());
    }

    public function testHasMandate(): void
    {
        $this->assertFalse((new SetupIntent('seti_no_mandate'))->hasMandate());

        $this->setupIntent->setMandateId('mandate_123');
        $this->assertTrue($this->setupIntent->hasMandate());
    }

    public function testHasPaymentMethod(): void
    {
        $this->assertFalse((new SetupIntent('seti_no_pm'))->hasPaymentMethod());
        $this->assertTrue($this->setupIntent->hasPaymentMethod());
    }

    public function testToArray(): void
    {
        $array = $this->setupIntent->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('seti_123', $array['id']);
        $this->assertEquals(SetupIntent::STATUS_SUCCEEDED, $array['status']);
        $this->assertEquals('cus_123', $array['customerId']);
        $this->assertEquals('pm_123', $array['paymentMethodId']);
        $this->assertArrayHasKey('createdAt', $array);
    }

    public function testFromArray(): void
    {
        $data = [
            'id' => 'seti_from',
            'status' => 'succeeded',
            'customerId' => 'cus_from',
            'paymentMethodId' => 'pm_from',
            'clientSecret' => 'secret_from',
            'usage' => 'on_session',
            'description' => 'From array',
            'mandateId' => 'mandate_from',
            'paymentMethodTypes' => ['card', 'sepa_debit'],
            'cancellationReason' => null,
            'metadata' => ['key' => 'value'],
            'createdAt' => 1234567890,
        ];

        $si = SetupIntent::fromArray($data);

        $this->assertEquals('seti_from', $si->getId());
        $this->assertEquals('succeeded', $si->getStatus());
        $this->assertEquals('cus_from', $si->getCustomerId());
        $this->assertEquals('pm_from', $si->getPaymentMethodId());
        $this->assertEquals('secret_from', $si->getClientSecret());
        $this->assertEquals('on_session', $si->getUsage());
        $this->assertEquals('From array', $si->getDescription());
        $this->assertEquals('mandate_from', $si->getMandateId());
        $this->assertEquals(['card', 'sepa_debit'], $si->getPaymentMethodTypes());
        $this->assertEquals(['key' => 'value'], $si->getMetadata());
        $this->assertEquals(1234567890, $si->getCreatedAt());
    }

    public function testFromArrayWithStripeFormat(): void
    {
        $data = [
            'id' => 'seti_stripe',
            'status' => 'requires_payment_method',
            'customer' => 'cus_stripe',
            'payment_method' => 'pm_stripe',
            'client_secret' => 'seti_stripe_secret_xxx',
            'usage' => 'off_session',
            'mandate' => 'mandate_stripe',
            'payment_method_types' => ['card'],
            'cancellation_reason' => 'abandoned',
            'last_setup_error' => ['code' => 'card_declined', 'message' => 'Declined'],
            'next_action' => ['type' => 'redirect_to_url'],
            'created' => 1234567890,
        ];

        $si = SetupIntent::fromArray($data);

        $this->assertEquals('seti_stripe', $si->getId());
        $this->assertEquals('cus_stripe', $si->getCustomerId());
        $this->assertEquals('pm_stripe', $si->getPaymentMethodId());
        $this->assertEquals('seti_stripe_secret_xxx', $si->getClientSecret());
        $this->assertEquals('mandate_stripe', $si->getMandateId());
        $this->assertEquals(['card'], $si->getPaymentMethodTypes());
        $this->assertEquals('abandoned', $si->getCancellationReason());
        $this->assertEquals('Declined', $si->getErrorMessage());
        $this->assertEquals('card_declined', $si->getErrorCode());
        $this->assertEquals(['type' => 'redirect_to_url'], $si->getNextAction());
        $this->assertEquals(1234567890, $si->getCreatedAt());
    }

    public function testFromArrayWithObjectCustomer(): void
    {
        $data = [
            'id' => 'seti_obj',
            'customer' => ['id' => 'cus_obj', 'name' => 'Test'],
            'payment_method' => ['id' => 'pm_obj', 'type' => 'card'],
        ];

        $si = SetupIntent::fromArray($data);

        $this->assertEquals('cus_obj', $si->getCustomerId());
        $this->assertEquals('pm_obj', $si->getPaymentMethodId());
    }

    public function testStatusConstants(): void
    {
        $this->assertEquals('requires_payment_method', SetupIntent::STATUS_REQUIRES_PAYMENT_METHOD);
        $this->assertEquals('requires_confirmation', SetupIntent::STATUS_REQUIRES_CONFIRMATION);
        $this->assertEquals('requires_action', SetupIntent::STATUS_REQUIRES_ACTION);
        $this->assertEquals('processing', SetupIntent::STATUS_PROCESSING);
        $this->assertEquals('canceled', SetupIntent::STATUS_CANCELED);
        $this->assertEquals('succeeded', SetupIntent::STATUS_SUCCEEDED);
    }

    public function testUsageConstants(): void
    {
        $this->assertEquals('on_session', SetupIntent::USAGE_ON_SESSION);
        $this->assertEquals('off_session', SetupIntent::USAGE_OFF_SESSION);
    }

    public function testCancellationConstants(): void
    {
        $this->assertEquals('abandoned', SetupIntent::CANCELLATION_ABANDONED);
        $this->assertEquals('requested_by_customer', SetupIntent::CANCELLATION_REQUESTED_BY_CUSTOMER);
        $this->assertEquals('duplicate', SetupIntent::CANCELLATION_DUPLICATE);
    }

    public function testFluentInterface(): void
    {
        $result = $this->setupIntent
            ->setId('seti_fluent')
            ->setStatus(SetupIntent::STATUS_PROCESSING)
            ->setCustomerId('cus_fluent')
            ->setUsage(SetupIntent::USAGE_ON_SESSION);

        $this->assertSame($this->setupIntent, $result);
        $this->assertEquals('seti_fluent', $this->setupIntent->getId());
    }
}
