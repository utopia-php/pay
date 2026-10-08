<?php

declare(strict_types=1);

namespace Utopia\Pay\Tests\Adapter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Utopia\Pay\Adapter\Stripe;
use Utopia\Pay\Address;
use Utopia\Pay\CardDetails;
use Utopia\Pay\CardMandate;
use Utopia\Pay\Exception;
use Utopia\Pay\Payment\Options;
use Utopia\Pay\Payment\Status;
use Utopia\Pay\Setup\Status as SetupStatus;
use Utopia\Psr7\Response;
use Utopia\Psr7\Stream;

final class StripeTypesTest extends TestCase
{
    public function testOnSessionChargeAndExpandedReferences(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->willReturnCallback(
            function (RequestInterface $request): ResponseInterface {
                parse_str((string) $request->getBody(), $params);
                $this->assertSame('false', $params['off_session']);
                $this->assertSame('false', $params['confirm']);
                $this->assertSame(['invoiceId' => 'invoice'], $params['metadata']);
                return new Response(200, body: new Stream('{"id":"pi_live","status":"requires_confirmation","amount":1200,"amount_received":0,"currency":"usd","client_secret":"secret","payment_method":{"id":"pm_expanded"},"charges":{"data":[{"id":"ch_old","amount_refunded":500}]}}'));
            }
        );
        $payment = new Stripe('sk_test', client: $client)->purchase(1200, 'cus', 'pm', new Options(offSession: false, confirm: false, metadata: ['invoiceId' => 'invoice']));
        $this->assertSame(Status::RequiresConfirmation, $payment->status);
        $this->assertSame('pm_expanded', $payment->paymentMethodId);
        $this->assertSame(1200, $payment->amount);
        $this->assertSame('secret', $payment->clientSecret);
        $this->assertSame(500, $payment->charges[0]->amountRefunded);
    }

    public function testCardAndAddressAreSerializedAndReturnedAsValues(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->exactly(3))->method('sendRequest')->willReturnCallback(
            function (RequestInterface $request): ResponseInterface {
                parse_str((string) $request->getBody(), $params);
                switch ($request->getUri()->getPath()) {
                    case '/v1/payment_methods':
                        $this->assertSame('card', $params['type']);
                        $this->assertSame(['number' => '4242424242424242', 'exp_month' => '8', 'exp_year' => '2031', 'cvc' => '123'], $params['card']);
                        break;
                    case '/v1/payment_methods/pm_created/attach':
                        $this->assertSame('cus', $params['customer']);
                        break;
                    case '/v1/payment_methods/pm_created':
                        $this->assertSame(['address' => ['city' => 'Berlin', 'country' => 'DE', 'postal_code' => '10115']], $params['billing_details']);
                        break;
                    default:
                        $this->fail('Unexpected operation');
                }
                return new Response(200, body: new Stream('{"id":"pm_created","card":{"brand":"visa","country":"DE","last4":"4242","exp_month":8,"exp_year":2031},"billing_details":{"address":{"city":"Berlin","country":"DE","postal_code":"10115"}}}'));
            }
        );
        $stripe = new Stripe('sk_test', client: $client);
        $paymentMethod = $stripe->createPaymentMethod('cus', new CardDetails('4242424242424242', 8, 2031, '123'));
        $this->assertSame('4242', $paymentMethod->card?->last4);
        $updated = $stripe->updatePaymentMethodBillingDetails($paymentMethod->id, address: new Address('Berlin', 'DE', postalCode: '10115'));
        $this->assertSame('Berlin', $updated->billingAddress?->city);
        $this->assertSame('10115', $updated->billingAddress->postalCode);
    }

    public function testSetupMandateAndTypedLists(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->exactly(3))->method('sendRequest')->willReturnCallback(
            function (RequestInterface $request): ResponseInterface {
                if ($request->getMethod() === 'POST') {
                    parse_str((string) $request->getBody(), $params);
                    $this->assertArrayNotHasKey('payment_method_types', $params);
                    $this->assertSame(['enabled' => 'true'], $params['automatic_payment_methods']);
                    $this->assertIsArray($params['payment_method_options']);
                    $this->assertIsArray($params['payment_method_options']['card']);
                    $this->assertIsArray($params['payment_method_options']['card']['mandate_options']);
                    $this->assertSame('15000', $params['payment_method_options']['card']['mandate_options']['amount']);
                    $this->assertSame(['india'], $params['payment_method_options']['card']['mandate_options']['supported_types']);
                    return new Response(200, body: new Stream('{"id":"seti","status":"requires_payment_method","client_secret":"setup_secret"}'));
                }
                $this->assertSame('GET', $request->getMethod());
                $this->assertSame('', (string) $request->getBody());
                if ($request->getUri()->getPath() === '/v1/setup_intents') {
                    parse_str($request->getUri()->getQuery(), $filters);
                    $this->assertSame('cus', $filters['customer']);
                    return new Response(200, body: new Stream('{"data":[{"id":"seti_saved","status":"succeeded","mandate":{"id":"mandate_saved"},"payment_method":"pm_saved"}]}'));
                }
                return new Response(200, body: new Stream('{"data":[{"id":"dp","payment_intent":"pi","metadata":{"invoiceId":"invoice"}}]}'));
            }
        );
        $stripe = new Stripe('sk_test', client: $client);
        $setupIntent = $stripe->createFuturePayment('cus', mandate: new CardMandate('reference', 15000, 'USD', 1723597289), paymentMethodConfiguration: 'pmc');
        $this->assertSame(SetupStatus::RequiresPaymentMethod, $setupIntent->status);
        $this->assertSame('setup_secret', $setupIntent->clientSecret);
        $saved = $stripe->listFuturePayment('cus');
        $this->assertSame(SetupStatus::Succeeded, $saved[0]->status);
        $this->assertSame('mandate_saved', $saved[0]->mandateId);
        $disputes = $stripe->listDisputes();
        $this->assertSame('pi', $disputes[0]->paymentId);
        $this->assertSame('invoice', $disputes[0]->metadata['invoiceId']);
    }

    /** @return iterable<string, array{string}> */
    public static function invalidPayments(): iterable
    {
        yield 'invalid JSON' => ['not json'];
        yield 'scalar' => ['false'];
        $valid = ['id' => 'pi', 'amount' => 1200, 'amount_received' => 0, 'currency' => 'usd', 'status' => 'succeeded'];
        foreach (array_keys($valid) as $field) {
            $missing = $valid;
            unset($missing[$field]);
            yield "missing $field" => [json_encode($missing, JSON_THROW_ON_ERROR)];
            yield "null $field" => [json_encode(array_replace($valid, [$field => null]), JSON_THROW_ON_ERROR)];
        }
        yield 'incorrect amount type' => [json_encode(array_replace($valid, ['amount' => '1200']), JSON_THROW_ON_ERROR)];
        yield 'unknown status' => [json_encode(array_replace($valid, ['status' => 'invented']), JSON_THROW_ON_ERROR)];
        yield 'invalid reference' => [json_encode($valid + ['payment_method' => 123], JSON_THROW_ON_ERROR)];
    }

    #[DataProvider('invalidPayments')]
    public function testInvalidResponsesCannotBecomePayments(string $body): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->willReturn(new Response(200, body: new Stream($body)));
        try {
            new Stripe('sk_test', client: $client)->getPayment('pi');
            $this->fail('An invalid response must not become a payment');
        } catch (Exception $exception) {
            $this->assertSame(502, $exception->getCode());
            $this->assertNull($exception->error);
        }
    }

    public function testFailureWithAnUnexpandedPaymentKeepsItsReference(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->willReturn(new Response(402, body: new Stream('{"error":{"code":"card_declined","decline_code":"new_processor_decline","type":"card_error","payment_intent":"pi_failed"}}')));
        try {
            new Stripe('sk_test', client: $client)->purchase(1200, 'cus', 'pm');
            $this->fail('The declined charge must throw');
        } catch (Exception $exception) {
            $this->assertSame('new_processor_decline', $exception->type);
            $this->assertSame('pi_failed', $exception->error?->paymentId);
            $this->assertNull($exception->error->payment);
        }
    }

    public function testMalformedCreatedMethodIsNeverAttached(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->willReturnCallback(
            function (RequestInterface $request): ResponseInterface {
                $this->assertSame('/v1/payment_methods', $request->getUri()->getPath());
                return new Response(200, body: new Stream('{"id":""}'));
            }
        );
        try {
            new Stripe('sk_test', client: $client)->createPaymentMethod('cus', new CardDetails('4242424242424242', 8, 2031, '123'));
            $this->fail('A method without an ID must never be attached');
        } catch (Exception $exception) {
            $this->assertSame(502, $exception->getCode());
        }
    }
}
