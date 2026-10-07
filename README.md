# Utopia Pay

> [!IMPORTANT]
> This repository is a read-only mirror of `packages/pay` in Appwrite's private Cloud repository (appwrite-labs/cloud). Development happens there, so pull requests and issues opened here are closed automatically.

Billing objects and payment processor adapters (Stripe) for PHP, maintained by the [Appwrite team](https://appwrite.io).

## Getting started

```bash
composer require utopia-php/pay
```

```php
use Utopia\Pay\Adapter\Stripe;
use Utopia\Pay\Pay;

$pay = new Pay(new Stripe('SECRET_KEY', currency: 'USD'));

$customer = $pay->createCustomer('Customer One', 'customer@example.com');
$purchase = $pay->purchase(5000, $customer->id, $paymentMethodId);
```

`authorize()` holds funds that `capture()` charges or `cancelAuthorization()` releases; customers, payment methods, refunds, future payments (setup intents) and disputes have their own methods on `Pay`.

Configuration is supplied in constructors. Billing values are readonly: `Invoice::finalize()` returns a new invoice, and `Credit::useCredits()` returns the remaining credit balance. Keep the returned value; the original is unchanged. Exception details are available through readonly `$exception->type` and `$exception->error` properties.

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

Processor methods expose the fields Pay uses through readonly payment, customer, payment-method, setup-intent, mandate or refund objects. List methods return lists of objects. Invoice/credit statuses and discount types are also backed enums; `toArray()` writes their string values for storage. Processor JSON is validated on entry. Decline codes, IDs, currency codes and metadata keys remain strings.

This replaces the raw array API: use object properties instead of array offsets, typed request objects instead of arbitrary processor parameter bags, and `$exception->error?->payment` or `paymentId` instead of error metadata arrays. The supported card request fields and subscription mandate policy are explicit in `CardDetails` and `CardMandate`.

Pay is checked at PHPStan's maximum level over source and tests, with no baseline or ignored errors.

## Tests

```bash
composer test                                       # unit tier, no network
STRIPE_SECRET=sk_test_... phpunit --group stripe    # calls Stripe's test mode
```

## License

[MIT](LICENSE)
