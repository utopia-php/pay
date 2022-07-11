# pay
Lite &amp; fast micro PHP payments abstraction library that is **easy to use**.

## Getting Started

Get Secret Key and Publishable Key from your Stripe Account.

```php
// stripe tests
$pay = new Pay(new Stripe('PUBLISHABLE_KEY', 'SECRET_KEY'));

$res = $pay->createCustomer('Customer One', 'customer@gmail.com');

$pay->setCurrency('INR');
$res = $pay->purchase(
    '5000',
    $res['id'],
    null,
    [
        'description' => 'some countries require descriptions'
    ]
);

var_dump($res);
```