<?php

declare(strict_types=1);

namespace Utopia\Pay\Tests\Adapter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Utopia\Pay\Adapter\Stripe;
use Utopia\Pay\Exception;
use Utopia\Pay\Exception\AuthenticationRequired;
use Utopia\Pay\Exception\Conflict;
use Utopia\Pay\Exception\Declined;
use Utopia\Pay\Exception\InvalidRequest;
use Utopia\Pay\Exception\InvalidResponse;
use Utopia\Pay\Exception\NotFound;
use Utopia\Pay\Exception\PermissionDenied;
use Utopia\Pay\Exception\ProcessorFailure;
use Utopia\Pay\Exception\RateLimited;
use Utopia\Pay\Exception\TransportFailure;
use Utopia\Psr7\Response;
use Utopia\Psr7\Stream;

final class StripeFailuresTest extends TestCase
{
    /** @return iterable<string, array{int, string, ?string, class-string<Exception>}> */
    public static function failures(): iterable
    {
        yield 'authentication challenge' => [402, 'card_error', 'authentication_required', AuthenticationRequired::class];
        yield 'new decline reason' => [402, 'card_error', 'new_decline_reason', Declined::class];
        yield 'invalid parameters' => [400, 'invalid_request_error', null, InvalidRequest::class];
        yield 'idempotency conflict' => [400, 'idempotency_error', null, Conflict::class];
        yield 'API failure' => [400, 'api_error', null, ProcessorFailure::class];
        yield 'unknown error type' => [400, 'new_error_type', null, Exception::class];
        yield 'invalid API key' => [401, 'invalid_request_error', null, PermissionDenied::class];
        yield 'permission refused' => [403, 'invalid_request_error', null, PermissionDenied::class];
        yield 'missing resource' => [404, 'invalid_request_error', null, NotFound::class];
        yield 'HTTP conflict' => [409, 'api_error', null, Conflict::class];
        yield 'rate limited' => [429, 'api_error', null, RateLimited::class];
        yield 'dependency failed' => [424, 'api_error', null, ProcessorFailure::class];
        yield 'server failure overrides decline' => [503, 'card_error', 'authentication_required', ProcessorFailure::class];
    }

    /** @param class-string<Exception> $failure */
    #[DataProvider('failures')]
    public function testProcessorFailuresHaveCatchableTypesAndRetainDiagnostics(int $status, string $type, ?string $declineCode, string $failure): void
    {
        $body = json_encode(['error' => ['type' => $type, 'code' => 'processor_code', 'decline_code' => $declineCode, 'payment_intent' => 'pi_failed']], JSON_THROW_ON_ERROR);
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->willReturn(new Response($status, body: new Stream($body))->withHeader('Request-Id', 'req_failure'));

        try {
            new Stripe('sk_test', client: $client)->purchase(1200, 'cus', 'pm');
            $this->fail('A processor failure must not return a payment');
        } catch (Exception $exception) {
            $this->assertInstanceOf($failure, $exception);
            $this->assertSame($status, $exception->getCode());
            $this->assertSame('req_failure', $exception->requestId);
            $this->assertSame($type, $exception->error?->type);
            $this->assertSame($declineCode, $exception->error->declineCode);
            $this->assertSame('processor_code', $exception->error->code);
            $this->assertSame('pi_failed', $exception->error->paymentId);
            $this->assertSame($type === 'card_error' && $declineCode !== null ? $declineCode : 'processor_code', $exception->type);
        }
    }

    public function testTransportFailurePreservesCauseWithoutClaimingADeclineOrRetrying(): void
    {
        $cause = new class ('Connection interrupted') extends \RuntimeException implements ClientExceptionInterface {};
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->willThrowException($cause);

        try {
            new Stripe('sk_test', client: $client)->purchase(1200, 'cus', 'pm');
            $this->fail('A transport failure must not return a payment');
        } catch (ProcessorFailure $processorFailure) {
            $this->assertInstanceOf(TransportFailure::class, $processorFailure);
            $this->assertSame($cause, $processorFailure->getPrevious());
            $this->assertSame(0, $processorFailure->getCode());
            $this->assertNull($processorFailure->error);
            $this->assertNull($processorFailure->requestId);
        }
    }

    /** @return iterable<string, array{string}> */
    public static function malformedErrors(): iterable
    {
        yield 'invalid JSON' => ['not json'];
        yield 'scalar response' => ['false'];
        yield 'wrong error shape' => ['{"error":"failed"}'];
        yield 'wrong code type' => ['{"error":{"code":123}}'];
        yield 'malformed expanded payment' => ['{"error":{"payment_intent":{"id":"pi_failed"}}}'];
    }

    #[DataProvider('malformedErrors')]
    public function testMalformedFailuresKeepHttpStatusRequestIdAndCause(string $body): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->willReturn(new Response(503, body: new Stream($body))->withHeader('Request-Id', 'req_malformed'));

        try {
            new Stripe('sk_test', client: $client)->purchase(1200, 'cus', 'pm');
            $this->fail('Malformed errors must not return a payment');
        } catch (ProcessorFailure $processorFailure) {
            $this->assertInstanceOf(InvalidResponse::class, $processorFailure);
            $this->assertSame(503, $processorFailure->getCode());
            $this->assertSame('req_malformed', $processorFailure->requestId);
            $this->assertInstanceOf(\Throwable::class, $processorFailure->getPrevious());
            $this->assertNull($processorFailure->error);
        }
    }
}
