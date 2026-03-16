<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Pay\Webhook\WebhookEvent;

class WebhookEventTest extends TestCase
{
    private WebhookEvent $event;

    protected function setUp(): void
    {
        $this->event = new WebhookEvent(
            'evt_123',
            'payment_intent.succeeded',
            ['object' => ['id' => 'pi_123', 'amount' => 1000]],
            'stripe'
        );
    }

    public function testConstructor(): void
    {
        $this->assertEquals('evt_123', $this->event->getId());
        $this->assertEquals('payment_intent.succeeded', $this->event->getType());
        $this->assertEquals('stripe', $this->event->getProvider());
        $this->assertNotEmpty($this->event->getData());
        $this->assertNotNull($this->event->getCreatedAt());
    }

    public function testConstructorDefaults(): void
    {
        $event = new WebhookEvent('evt_default', 'test.event');

        $this->assertEquals([], $event->getData());
        $this->assertNull($event->getProvider());
        $this->assertNull($event->getApiVersion());
        $this->assertFalse($event->isLivemode());
        $this->assertEquals(0, $event->getPendingWebhooks());
        $this->assertNull($event->getRequestId());
    }

    public function testConstructorWithAllParameters(): void
    {
        $event = new WebhookEvent(
            'evt_full',
            'charge.succeeded',
            ['object' => ['id' => 'ch_123']],
            'stripe',
            '2024-01-01',
            true,
            1234567890,
            3,
            'req_123'
        );

        $this->assertEquals('evt_full', $event->getId());
        $this->assertEquals('charge.succeeded', $event->getType());
        $this->assertEquals('stripe', $event->getProvider());
        $this->assertEquals('2024-01-01', $event->getApiVersion());
        $this->assertTrue($event->isLivemode());
        $this->assertEquals(1234567890, $event->getCreatedAt());
        $this->assertEquals(3, $event->getPendingWebhooks());
        $this->assertEquals('req_123', $event->getRequestId());
    }

    public function testGetObject(): void
    {
        $object = $this->event->getObject();
        $this->assertEquals('pi_123', $object['id']);
        $this->assertEquals(1000, $object['amount']);

        // Without nested object
        $event = new WebhookEvent('evt_flat', 'test', ['id' => 'test_123']);
        $this->assertEquals(['id' => 'test_123'], $event->getObject());
    }

    public function testTypeContains(): void
    {
        $this->assertTrue($this->event->typeContains('payment'));
        $this->assertTrue($this->event->typeContains('succeeded'));
        $this->assertTrue($this->event->typeContains('PAYMENT')); // case insensitive
        $this->assertFalse($this->event->typeContains('refund'));
    }

    public function testIsPaymentEvent(): void
    {
        $this->assertTrue($this->event->isPaymentEvent());

        $charge = new WebhookEvent('evt_1', 'charge.captured');
        $this->assertTrue($charge->isPaymentEvent());

        $transaction = new WebhookEvent('evt_2', 'transaction.created');
        $this->assertTrue($transaction->isPaymentEvent());

        $refund = new WebhookEvent('evt_3', 'refund.created');
        $this->assertFalse($refund->isPaymentEvent());
    }

    public function testIsCustomerEvent(): void
    {
        $event = new WebhookEvent('evt_1', 'customer.created');
        $this->assertTrue($event->isCustomerEvent());

        $this->assertFalse($this->event->isCustomerEvent());
    }

    public function testIsSubscriptionEvent(): void
    {
        $event = new WebhookEvent('evt_1', 'customer.subscription.created');
        $this->assertTrue($event->isSubscriptionEvent());

        $this->assertFalse($this->event->isSubscriptionEvent());
    }

    public function testIsDisputeEvent(): void
    {
        $event = new WebhookEvent('evt_1', 'charge.dispute.created');
        $this->assertTrue($event->isDisputeEvent());

        $chargeback = new WebhookEvent('evt_2', 'chargeback.created');
        $this->assertTrue($chargeback->isDisputeEvent());

        $this->assertFalse($this->event->isDisputeEvent());
    }

    public function testIsRefundEvent(): void
    {
        $event = new WebhookEvent('evt_1', 'charge.refunded');
        $this->assertTrue($event->isRefundEvent());

        $event2 = new WebhookEvent('evt_2', 'refund.created');
        $this->assertTrue($event2->isRefundEvent());

        $this->assertFalse($this->event->isRefundEvent());
    }

    public function testIsInvoiceEvent(): void
    {
        $event = new WebhookEvent('evt_1', 'invoice.paid');
        $this->assertTrue($event->isInvoiceEvent());

        $this->assertFalse($this->event->isInvoiceEvent());
    }

    public function testIsSetupEvent(): void
    {
        $event = new WebhookEvent('evt_1', 'setup_intent.succeeded');
        $this->assertTrue($event->isSetupEvent());

        $mandate = new WebhookEvent('evt_2', 'mandate.updated');
        $this->assertTrue($mandate->isSetupEvent());

        $this->assertFalse($this->event->isSetupEvent());
    }

    public function testIsPaymentMethodEvent(): void
    {
        $event = new WebhookEvent('evt_1', 'payment_method.attached');
        $this->assertTrue($event->isPaymentMethodEvent());

        // Also matches 'payment_intent.succeeded' because it contains 'payment' — but isPaymentMethodEvent checks payment_method specifically
        // Actually, payment_intent.succeeded doesn't contain 'payment_method' literally
        // But it does contain 'card' or 'source' checks too
    }

    public function testIsSuccessEvent(): void
    {
        $this->assertTrue($this->event->isSuccessEvent()); // payment_intent.succeeded

        $paid = new WebhookEvent('evt_1', 'invoice.paid');
        $this->assertTrue($paid->isSuccessEvent());

        $captured = new WebhookEvent('evt_2', 'charge.captured');
        $this->assertTrue($captured->isSuccessEvent());

        $completed = new WebhookEvent('evt_3', 'checkout.session.completed');
        $this->assertTrue($completed->isSuccessEvent());

        $failed = new WebhookEvent('evt_4', 'payment_intent.payment_failed');
        $this->assertFalse($failed->isSuccessEvent());
    }

    public function testIsFailureEvent(): void
    {
        $failed = new WebhookEvent('evt_1', 'payment_intent.payment_failed');
        $this->assertTrue($failed->isFailureEvent());

        $declined = new WebhookEvent('evt_2', 'charge.declined');
        $this->assertTrue($declined->isFailureEvent());

        $this->assertFalse($this->event->isFailureEvent());
    }

    public function testRequiresAction(): void
    {
        $action = new WebhookEvent('evt_1', 'payment_intent.requires_action');
        $this->assertTrue($action->requiresAction());

        $pending = new WebhookEvent('evt_2', 'payment_intent.pending');
        $this->assertTrue($pending->requiresAction());

        $disputeCreated = new WebhookEvent('evt_3', 'charge.dispute.created');
        $this->assertTrue($disputeCreated->requiresAction());

        $this->assertFalse($this->event->requiresAction());
    }

    public function testGetAction(): void
    {
        $this->assertEquals('succeeded', $this->event->getAction());

        $event = new WebhookEvent('evt_1', 'charge.dispute.created');
        $this->assertEquals('created', $event->getAction());

        $event2 = new WebhookEvent('evt_2', 'simple_event');
        $this->assertEquals('simple_event', $event2->getAction());
    }

    public function testGetResourceType(): void
    {
        $this->assertEquals('payment_intent', $this->event->getResourceType());

        $event = new WebhookEvent('evt_1', 'charge.dispute.created');
        $this->assertEquals('charge', $event->getResourceType());
    }

    public function testGetCategory(): void
    {
        $this->assertEquals(WebhookEvent::CATEGORY_PAYMENT, $this->event->getCategory());

        $refund = new WebhookEvent('evt_1', 'refund.created');
        $this->assertEquals(WebhookEvent::CATEGORY_REFUND, $refund->getCategory());

        $dispute = new WebhookEvent('evt_2', 'dispute.created');
        $this->assertEquals(WebhookEvent::CATEGORY_DISPUTE, $dispute->getCategory());

        $subscription = new WebhookEvent('evt_3', 'customer.subscription.updated');
        $this->assertEquals(WebhookEvent::CATEGORY_SUBSCRIPTION, $subscription->getCategory());

        $invoice = new WebhookEvent('evt_4', 'invoice.paid');
        $this->assertEquals(WebhookEvent::CATEGORY_INVOICE, $invoice->getCategory());

        $setup = new WebhookEvent('evt_5', 'setup_intent.succeeded');
        $this->assertEquals(WebhookEvent::CATEGORY_SETUP, $setup->getCategory());

        $payout = new WebhookEvent('evt_6', 'payout.paid');
        $this->assertEquals(WebhookEvent::CATEGORY_PAYOUT, $payout->getCategory());
    }

    public function testToArray(): void
    {
        $array = $this->event->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('evt_123', $array['id']);
        $this->assertEquals('payment_intent.succeeded', $array['type']);
        $this->assertEquals('stripe', $array['provider']);
        $this->assertArrayHasKey('data', $array);
        $this->assertArrayHasKey('createdAt', $array);
        $this->assertArrayHasKey('livemode', $array);
        $this->assertArrayHasKey('pendingWebhooks', $array);
    }

    public function testFromArray(): void
    {
        $data = [
            'id' => 'evt_from',
            'type' => 'charge.refunded',
            'data' => ['object' => ['id' => 'ch_123']],
            'provider' => 'stripe',
            'apiVersion' => '2024-01-01',
            'livemode' => true,
            'createdAt' => 1234567890,
            'pendingWebhooks' => 2,
            'requestId' => 'req_from',
        ];

        $event = WebhookEvent::fromArray($data);

        $this->assertEquals('evt_from', $event->getId());
        $this->assertEquals('charge.refunded', $event->getType());
        $this->assertEquals('stripe', $event->getProvider());
        $this->assertEquals('2024-01-01', $event->getApiVersion());
        $this->assertTrue($event->isLivemode());
        $this->assertEquals(1234567890, $event->getCreatedAt());
        $this->assertEquals(2, $event->getPendingWebhooks());
        $this->assertEquals('req_from', $event->getRequestId());
    }

    public function testFromArrayWithStripeFormat(): void
    {
        $data = [
            'id' => 'evt_stripe',
            'type' => 'payment_intent.succeeded',
            'data' => ['object' => ['id' => 'pi_stripe']],
            'api_version' => '2024-01-01',
            'livemode' => false,
            'created' => 1234567890,
            'pending_webhooks' => 1,
            'request' => ['id' => 'req_stripe'],
        ];

        $event = WebhookEvent::fromArray($data, 'stripe');

        $this->assertEquals('evt_stripe', $event->getId());
        $this->assertEquals('stripe', $event->getProvider());
        $this->assertEquals('2024-01-01', $event->getApiVersion());
        $this->assertEquals(1234567890, $event->getCreatedAt());
        $this->assertEquals(1, $event->getPendingWebhooks());
        $this->assertEquals('req_stripe', $event->getRequestId());
    }

    public function testFromArrayProviderOverride(): void
    {
        $data = [
            'id' => 'evt_test',
            'type' => 'test',
            'provider' => 'paypal',
        ];

        // Provider parameter takes precedence
        $event = WebhookEvent::fromArray($data, 'stripe');
        $this->assertEquals('stripe', $event->getProvider());

        // Falls back to data provider
        $event2 = WebhookEvent::fromArray($data);
        $this->assertEquals('paypal', $event2->getProvider());
    }

    public function testCategoryConstants(): void
    {
        $this->assertEquals('payment', WebhookEvent::CATEGORY_PAYMENT);
        $this->assertEquals('refund', WebhookEvent::CATEGORY_REFUND);
        $this->assertEquals('customer', WebhookEvent::CATEGORY_CUSTOMER);
        $this->assertEquals('payment_method', WebhookEvent::CATEGORY_PAYMENT_METHOD);
        $this->assertEquals('dispute', WebhookEvent::CATEGORY_DISPUTE);
        $this->assertEquals('subscription', WebhookEvent::CATEGORY_SUBSCRIPTION);
        $this->assertEquals('invoice', WebhookEvent::CATEGORY_INVOICE);
        $this->assertEquals('payout', WebhookEvent::CATEGORY_PAYOUT);
        $this->assertEquals('setup', WebhookEvent::CATEGORY_SETUP);
    }

    public function testActionConstants(): void
    {
        $this->assertEquals('created', WebhookEvent::ACTION_CREATED);
        $this->assertEquals('updated', WebhookEvent::ACTION_UPDATED);
        $this->assertEquals('deleted', WebhookEvent::ACTION_DELETED);
        $this->assertEquals('succeeded', WebhookEvent::ACTION_SUCCEEDED);
        $this->assertEquals('failed', WebhookEvent::ACTION_FAILED);
        $this->assertEquals('canceled', WebhookEvent::ACTION_CANCELED);
        $this->assertEquals('pending', WebhookEvent::ACTION_PENDING);
        $this->assertEquals('requires_action', WebhookEvent::ACTION_REQUIRES_ACTION);
        $this->assertEquals('refunded', WebhookEvent::ACTION_REFUNDED);
        $this->assertEquals('captured', WebhookEvent::ACTION_CAPTURED);
    }
}
