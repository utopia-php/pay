<?php

namespace Utopia\Pay\Tests\Adapter;

use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Utopia\Pay\Adapter\Stripe;
use Utopia\Pay\Exception;
use Utopia\Pay\Pay;
use Utopia\Psr7\Response;
use Utopia\Psr7\Stream;

class StripeConfigurationTest extends TestCase
{
    public function testConstructorConfigurationReachesRepeatedCharges(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->exactly(2))->method('sendRequest')->willReturnCallback(
            function (RequestInterface $request): ResponseInterface {
                $this->assertSame('Bearer sk_test_config', $request->getHeaderLine('Authorization'));
                parse_str((string) $request->getBody(), $params);
                $this->assertSame('EUR', $params['currency']);
                $this->assertSame('5000', $params['amount']);

                return (new Response(200, body: new Stream('{}')))->withHeader('Content-Type', 'application/json');
            }
        );
        $pay = new Pay(new Stripe('sk_test_config', currency: 'EUR', client: $client));
        $pay->purchase(5000, 'cus_current', 'pm_current');
        $pay->authorize(5000, 'cus_current', 'pm_current');
    }

    public function testProcessorFailureExposesDetailsForTheCaller(): void
    {
        $error = ['type' => 'card_error', 'code' => 'card_declined', 'decline_code' => 'authentication_required', 'message' => 'Authenticate this payment', 'payment_intent' => ['id' => 'pi_failed']];
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->willReturn(
            (new Response(402, body: new Stream(json_encode(['error' => $error], JSON_THROW_ON_ERROR))))->withHeader('Content-Type', 'application/json')
        );

        try {
            new Pay(new Stripe('sk_test_config', client: $client))->purchase(5000, 'cus_current', 'pm_current');
            $this->fail('The failed charge must throw');
        } catch (Exception $exception) {
            $this->assertSame(Exception::AUTHENTICATION_REQUIRED, $exception->type);
            $this->assertSame($error, $exception->metadata);
            $this->assertSame(402, $exception->getCode());
            $this->assertSame('Authenticate this payment', $exception->getMessage());
        }
    }
}
