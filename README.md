# Utopia Pay

> [!IMPORTANT]
> This repository is a read-only mirror of `packages/pay` in Appwrite's private Cloud repository (appwrite-labs/cloud). Development happens there, so pull requests and issues opened here are closed automatically.

Payment processor adapters (Stripe) and typed payment objects for PHP, maintained by the [Appwrite team](https://appwrite.io).

## Getting started

```bash
composer require utopia-php/pay
```

```php
use Utopia\Pay\Adapter\Stripe;
use Utopia\Pay\Adapter\Stripe\Currency;

$pay = new Stripe('SECRET_KEY', currency: Currency::USD);

$customer = $pay->createCustomer('Customer One', 'customer@example.com');
$purchase = $pay->purchase(5000, $customer->id, $paymentMethodId);
```

`authorize()` holds funds that `capture()` charges or `cancelAuthorization()` releases; customers, payment methods, refunds, future payments (setup intents) and disputes have their own methods on `Pay`.

`Pay` is the abstract processor contract; `Adapter\Stripe` implements it directly. Initialize Stripe at the application boundary and type consumers against `Pay`. The forwarding wrapper and the separate `Adapter` base class have been removed. Custom processors now extend `Pay`; direct Stripe callers use `listFuturePayment()` to match the existing Pay contract.

Configuration is supplied in constructors. Request and result objects are readonly. Catch `Utopia\Pay\Exception` for any Pay failure, or its specific subtypes for handling decisions. The legacy `$exception->type` processor code and `$exception->error` diagnostics remain readonly; `$exception->requestId` identifies an HTTP failure at the processor.

Payment methods use `CardDetails`, addresses use `Address`, and card setup mandates use `CardMandate`. Charge and retry options use `Payment\Options`:

```php
use Utopia\Pay\Payment\Options;
use Utopia\Pay\Payment\Status;

$payment = $pay->purchase(5000, $customer->id, $paymentMethodId,
    new Options(offSession: false, confirm: false, metadata: ['invoiceId' => 'invoice_1']));

if ($payment->status === Status::RequiresConfirmation) {
    $clientSecret = $payment->clientSecret;
}
```

Processor methods expose the fields Pay uses through readonly payment, customer, payment-method, setup-intent, mandate or refund objects. List methods return lists of objects. Processor statuses are backed enums. Processor JSON is validated on entry. Stripe constructor configuration uses `Adapter\Stripe\Currency`, backed by Stripe’s lowercase presentment currency codes. Currency availability still depends on the Stripe account and payment method. Shared Pay contracts and result objects keep currency codes as strings; decline codes, IDs and metadata keys also remain strings.

This replaces the raw array API: use object properties instead of array offsets, typed request objects instead of arbitrary processor parameter bags, and `$exception->error?->payment` or `paymentId` instead of error metadata arrays. The supported card request fields are explicit in `CardDetails`. Shared `CardMandate` holds readonly reference, amount, currency, start date and description fields; it no longer has `toArray()`. `Adapter\Stripe\CardMandate` translates that data into Stripe’s maximum-amount, sporadic India mandate policy and lowercase currency. Cloud continues to construct the shared mandate.

The unused `Invoice`, `Credit` and `Discount` models and their enums have been removed. Invoice calculation and lifecycle orchestration remain in the consuming application.

Stripe request vocabularies live under `Adapter\Stripe`: `PaymentMethodType`, `CaptureMethod`, `ErrorType` and `CardMandate\{AmountType, Interval, SupportedType}`. Direct Stripe setup callers can pass `PaymentMethodType` cases; the abstract Pay contract retains string payment-method names, which Stripe validates and translates at its boundary.

## Failures

The exception hierarchy lives entirely in Pay:

```text
Utopia\Pay\Exception
├── AuthenticationRequired
├── Declined
├── InvalidRequest
│   ├── PermissionDenied
│   ├── NotFound
│   └── Conflict
└── ProcessorFailure
    ├── RateLimited
    ├── TransportFailure
    └── InvalidResponse
```

Catch a leaf for a specific action or a parent for a group of failures. Authentication challenges are separate from declines. HTTP status takes precedence over Stripe error type for permission, resource, conflict, rate-limit and server failures. Unknown Stripe error types fall back to the root exception, preserving their raw type and codes in `error`; future decline codes still produce `Declined`. Malformed processor data raises `InvalidResponse`.

`getCode()` retains the processor HTTP status for HTTP error responses, uses 502 for invalid successful responses, and 0 when transport fails before a response. Locally rejected Stripe request values use 400. `getPrevious()` retains transport and parsing causes. Existing broad catches and processor-code comparisons keep working.

These classes describe failures, not retry policy. A transport failure, malformed response or server error can leave a charge’s outcome unknown. The adapter does not retry automatically; reconcile the payment or use a stable idempotency key before repeating a charge. The HTTP error envelope retains `requestId`; validation failures while hydrating successful result objects currently have no request ID.

Pay is checked at PHPStan's maximum level over source and tests, with no baseline or ignored errors. Package checks also enforce Rector's PHP 8.5 modernization, code quality, dead code, type declaration, style and PHPUnit suites through `rector.php`.

## Tests

```bash
composer test                                       # unit tier, no network
STRIPE_SECRET=sk_test_... phpunit --group stripe    # calls Stripe's test mode
```

## License

[MIT](LICENSE)
