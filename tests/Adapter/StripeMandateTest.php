<?php

declare(strict_types=1);

namespace Utopia\Pay\Tests\Adapter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Utopia\Pay\Adapter\Stripe;
use Utopia\Pay\Pay;
use Utopia\Psr7\Response;
use Utopia\Psr7\Stream;

class StripeMandateTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string, string, int, bool, 5?: bool}>
     */
    public static function mandates(): iterable
    {
        foreach (['purchase', 'retryPurchase', 'authorize'] as $operation) {
            yield "$operation matching" => [$operation, 'active', 'pm_current', 200, true];
            yield "$operation replaced card" => [$operation, 'active', 'pm_old', 200, false];
            yield "$operation inactive" => [$operation, 'inactive', 'pm_current', 200, false];
            yield "$operation unavailable" => [$operation, '', '', 404, false];
        }
        yield 'retry saved method' => ['retryPurchase', 'active', 'pm_current', 200, true, true];
        yield 'retry saved method mismatched' => ['retryPurchase', 'active', 'pm_old', 200, false, true];
    }

    #[DataProvider('mandates')]
    public function testOnlyUsesAnActiveMandateForTheChargedCard(string $operation, string $status, string $mandateMethod, int $lookupStatus, bool $keepMandate, bool $useSavedMethod = false): void
    {
        $charge = null;
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->exactly($useSavedMethod ? 3 : 2))->method('sendRequest')->willReturnCallback(
            function (RequestInterface $request) use (&$charge, $status, $mandateMethod, $lookupStatus): ResponseInterface {
                if ($request->getMethod() === 'GET' && $request->getUri()->getPath() === '/v1/payment_intents/pi_existing') {
                    return (new Response(200, body: new Stream('{"id":"pi_existing","amount":5000,"amount_received":0,"currency":"usd","status":"requires_confirmation","payment_method":"pm_current"}')))->withHeader('Content-Type', 'application/json');
                }
                if ($request->getMethod() === 'GET') {
                    $this->assertSame('/v1/mandates/mandate_saved', $request->getUri()->getPath());

                    return (new Response($lookupStatus, body: new Stream(json_encode($lookupStatus === 200 ? [
                        'id' => 'mandate_saved',
                        'status' => $status,
                        'payment_method' => $mandateMethod,
                    ] : ['error' => ['message' => 'No such mandate']], JSON_THROW_ON_ERROR))))->withHeader('Content-Type', 'application/json');
                }

                $charge = $request;

                return (new Response(200, body: new Stream('{"id":"pi_charged","amount":5000,"amount_received":5000,"currency":"usd","status":"succeeded"}')))->withHeader('Content-Type', 'application/json');
            }
        );
        $pay = new Pay(new Stripe('sk_test_probe', client: $client));
        $options = new \Utopia\Pay\Payment\Options(mandate: 'mandate_saved');
        if ($operation === 'retryPurchase') {
            $pay->retryPurchase('pi_existing', $useSavedMethod ? null : 'pm_current', $options);
        } else {
            $pay->$operation(5000, 'cus_current', 'pm_current', $options);
        }

        $this->assertInstanceOf(RequestInterface::class, $charge);
        $this->assertSame('POST', $charge->getMethod());
        $this->assertSame($operation === 'retryPurchase' ? '/v1/payment_intents/pi_existing/confirm' : '/v1/payment_intents', $charge->getUri()->getPath());
        parse_str((string) $charge->getBody(), $params);
        $this->assertSame($useSavedMethod ? null : 'pm_current', $params['payment_method'] ?? null);
        $this->assertSame($keepMandate ? 'mandate_saved' : null, $params['mandate'] ?? null);
    }

    public function testPurchaseWithoutAMandateDoesNotLookOneUp(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->willReturnCallback(
            function (RequestInterface $request): ResponseInterface {
                $this->assertSame('POST', $request->getMethod());
                parse_str((string) $request->getBody(), $params);
                $this->assertArrayNotHasKey('mandate', $params);
                $this->assertSame('pm_current', $params['payment_method']);

                return (new Response(200, body: new Stream('{"id":"pi_charged","amount":5000,"amount_received":5000,"currency":"usd","status":"succeeded"}')))->withHeader('Content-Type', 'application/json');
            }
        );
        new Pay(new Stripe('sk_test_probe', client: $client))->purchase(5000, 'cus_current', 'pm_current');
    }

    /** @return iterable<string, array{string}> */
    public static function malformedLookups(): iterable
    {
        yield 'mandate' => ['/v1/mandates/mandate_saved'];
        yield 'saved payment intent' => ['/v1/payment_intents/pi_existing'];
    }

    #[DataProvider('malformedLookups')]
    public function testMalformedLookupDoesNotPreventTheCharge(string $malformedPath): void
    {
        $charged = false;
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->exactly($malformedPath === '/v1/mandates/mandate_saved' ? 2 : 3))
            ->method('sendRequest')->willReturnCallback(
                function (RequestInterface $request) use ($malformedPath, &$charged): ResponseInterface {
                    if ($request->getMethod() === 'GET') {
                        $body = $request->getUri()->getPath() === $malformedPath
                            ? 'invalid json'
                            : '{"id":"mandate_saved","status":"active","payment_method":"pm_current"}';

                        return (new Response(200, body: new Stream($body)))->withHeader('Content-Type', 'application/json');
                    }

                    $this->assertSame('POST', $request->getMethod());
                    $this->assertSame('/v1/payment_intents/pi_existing/confirm', $request->getUri()->getPath());
                    parse_str((string) $request->getBody(), $params);
                    $this->assertArrayNotHasKey('mandate', $params);
                    $charged = true;

                    return (new Response(200, body: new Stream('{"id":"pi_charged","amount":5000,"amount_received":5000,"currency":"usd","status":"succeeded"}')))->withHeader('Content-Type', 'application/json');
                }
            );
        new Pay(new Stripe('sk_test_probe', client: $client))->retryPurchase('pi_existing', options: new \Utopia\Pay\Payment\Options(mandate: 'mandate_saved'));
        $this->assertTrue($charged);
    }

}
