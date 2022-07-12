# pay
Lite &amp; fast micro PHP payments abstraction library that is **easy to use**.

## Getting Started

Get Secret Key and Publishable Key from your Stripe Account.

```php
$pay = new Pay(new Stripe('PUBLISHABLE_KEY', 'SECRET_KEY'));

$customer = $pay->createCustomer('Customer One', 'customer@gmail.com');
\var_dump($customer);

$pay->setCurrency('INR');
$purchase = $pay->purchase(
    5000, // price
    $customer['id'], // customer ID
    null, // card ID
    [
        'description' => 'some countries require descriptions'
    ]
);

var_dump($purchase);
```