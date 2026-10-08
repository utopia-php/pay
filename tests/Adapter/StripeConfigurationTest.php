<?php

declare(strict_types=1);

namespace Utopia\Pay\Tests\Adapter;

use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Utopia\Pay\Adapter\Stripe;
use Utopia\Pay\Adapter\Stripe\Currency;
use Utopia\Pay\Exception;
use Utopia\Pay\Pay;
use Utopia\Psr7\Response;
use Utopia\Psr7\Stream;

final class StripeConfigurationTest extends TestCase
{
    private Pay $pay;

    public function testConstructorConfigurationReachesRepeatedCharges(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->exactly(2))->method('sendRequest')->willReturnCallback(
            function (RequestInterface $request): ResponseInterface {
                $this->assertSame('Bearer sk_test_config', $request->getHeaderLine('Authorization'));
                parse_str((string) $request->getBody(), $params);
                $this->assertSame('eur', $params['currency']);
                $this->assertSame('5000', $params['amount']);

                return new Response(200, body: new Stream('{"id":"pi_charged","amount":5000,"amount_received":5000,"currency":"eur","status":"succeeded"}'))->withHeader('Content-Type', 'application/json');
            }
        );
        $this->pay = new Stripe('sk_test_config', currency: Currency::EUR, client: $client);
        $this->assertSame('eur', $this->pay->purchase(5000, 'cus_current', 'pm_current')->currency);
        $this->assertSame('eur', $this->pay->authorize(5000, 'cus_current', 'pm_current')->currency);
    }

    public function testProcessorFailureExposesDetailsForTheCaller(): void
    {
        $error = ['type' => 'card_error', 'code' => 'card_declined', 'decline_code' => 'authentication_required', 'message' => 'Authenticate this payment', 'payment_intent' => ['id' => 'pi_failed', 'amount' => 5000, 'amount_received' => 0, 'currency' => 'usd', 'status' => 'requires_action']];
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->willReturn(
            new Response(402, body: new Stream(json_encode(['error' => $error], JSON_THROW_ON_ERROR)))->withHeader('Content-Type', 'application/json')
        );

        try {
            new Stripe('sk_test_config', client: $client)->purchase(5000, 'cus_current', 'pm_current');
            $this->fail('The failed charge must throw');
        } catch (Exception $exception) {
            $this->assertSame(Exception::AUTHENTICATION_REQUIRED, $exception->type);
            $this->assertSame('pi_failed', $exception->error?->payment?->id);
            $this->assertSame('authentication_required', $exception->error->declineCode);
            $this->assertSame('card_declined', $exception->error->code);
            $this->assertSame(402, $exception->getCode());
            $this->assertSame('Authenticate this payment', $exception->getMessage());
        }
    }
}
