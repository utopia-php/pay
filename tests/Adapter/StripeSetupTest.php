<?php

declare(strict_types=1);

namespace Utopia\Pay\Tests\Adapter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Utopia\Pay\Adapter\Stripe;
use Utopia\Pay\Adapter\Stripe\PaymentMethodType;
use Utopia\Pay\CardMandate;
use Utopia\Pay\Exception;
use Utopia\Pay\Exception\InvalidRequest;
use Utopia\Psr7\Response;
use Utopia\Psr7\Stream;

final class StripeSetupTest extends TestCase
{
    /** @return iterable<string, array{bool}> */
    public static function operations(): iterable
    {
        yield 'create' => [false];
        yield 'update' => [true];
    }

    #[DataProvider('operations')]
    public function testSharedMandateIsTranslatedForStripeOnCreateAndUpdate(bool $update): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->willReturnCallback(
            function (RequestInterface $request) use ($update): ResponseInterface {
                $this->assertSame($update ? '/v1/setup_intents/seti' : '/v1/setup_intents', $request->getUri()->getPath());
                parse_str((string) $request->getBody(), $params);
                $this->assertIsArray($params['payment_method_options']);
                $this->assertIsArray($params['payment_method_options']['card']);
                $this->assertSame([
                    'reference' => 'monthly', 'description' => 'Subscription', 'amount' => '15000',
                    'currency' => 'eur', 'start_date' => '1723597289', 'amount_type' => 'maximum',
                    'interval' => 'sporadic', 'supported_types' => ['india'],
                ], $params['payment_method_options']['card']['mandate_options']);
                if (!$update) {
                    $this->assertSame(['card'], $params['payment_method_types']);
                }
                return new Response(200, body: new Stream('{"id":"seti","status":"requires_payment_method"}'));
            }
        );
        $cardMandate = new CardMandate('monthly', 15000, 'EUR', 1723597289, 'Subscription');
        $stripe = new Stripe('sk_test', client: $client);
        $result = $update ? $stripe->updateFuturePayment('seti', mandate: $cardMandate) : $stripe->createFuturePayment('cus', mandate: $cardMandate);
        $this->assertSame('seti', $result->id);
        $this->assertSame('EUR', $cardMandate->currency);
    }

    public function testStripePaymentMethodEnumsAreSerializedAtTheBoundary(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->willReturnCallback(
            function (RequestInterface $request): ResponseInterface {
                parse_str((string) $request->getBody(), $params);
                $this->assertSame(['card', 'sepa_debit'], $params['payment_method_types']);
                return new Response(200, body: new Stream('{"id":"seti"}'));
            }
        );
        $setupIntent = new Stripe('sk_test', client: $client)->createFuturePayment('cus', paymentMethodTypes: [PaymentMethodType::Card, PaymentMethodType::SepaDebit]);
        $this->assertSame('seti', $setupIntent->id);
    }

    public function testUnsupportedStripeMandateCurrencyMakesNoRequest(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->never())->method('sendRequest');
        try {
            new Stripe('sk_test', client: $client)->createFuturePayment('cus', mandate: new CardMandate('monthly', 15000, 'XYZ', 1723597289));
            $this->fail('An unsupported mandate currency must not reach Stripe');
        } catch (Exception $exception) {
            $this->assertInstanceOf(InvalidRequest::class, $exception);
            $this->assertSame(400, $exception->getCode());
        }
    }
}
