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

$pay = new Pay(new Stripe('SECRET_KEY'));
$pay->setCurrency('USD');

$customer = $pay->createCustomer('Customer One', 'customer@example.com');
$purchase = $pay->purchase(5000, $customer['id'], $paymentMethodId);
```

`authorize()` holds funds that `capture()` charges or `cancelAuthorization()` releases; customers, payment methods, refunds, future payments (setup intents) and disputes have their own methods on `Pay`.

## Tests

```bash
composer test                                       # unit tier, no network
STRIPE_SECRET=sk_test_... phpunit --group stripe    # calls Stripe's test mode
```

## License

[MIT](LICENSE)
