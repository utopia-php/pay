# Utopia Pay

[![Build Status](https://travis-ci.org/utopia-php/pay.svg?branch=master)](https://travis-ci.com/utopia-php/pay)
![Total Downloads](https://img.shields.io/packagist/dt/utopia-php/pay.svg)
[![Discord](https://img.shields.io/discord/564160730845151244?label=discord)](https://appwrite.io/discord)

Utopia Pay library is simple and lite library for accepting payments. This library is aiming to be as simple and easy to learn and use. This library is maintained by the [Appwrite team](https://appwrite.io).

Although this library is part of the [Utopia Framework](https://github.com/utopia-php/framework), it is dependency free and can be used as standalone with any other PHP project or framework.

## Getting Started

Install using composer:
```bash
composer require utopia-php/pay
```

Get Secret Key and Publishable Key from your Stripe Account.

```php
require_once '../vendor/autoload.php';
use Utopia\Pay\Pay;
use Utopia\Pay\Adapter\Stripe;

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

## System Requirements

Utopia Pay requires PHP 8.0 or later. We recommend using the latest PHP version whenever possible.


## Contributing

All code contributions - including those of people having commit access - must go through a pull request and approved by a core developer before being merged. This is to ensure proper review of all the code.

Fork the project, create a feature branch, and send us a pull request.

You can refer to the [Contributing Guide](CONTRIBUTING.md) for more info.

### Testing

```
vendor/bin/phpunit --configuration phpunit.xml
```

## Copyright and license

The MIT License (MIT) [http://www.opensource.org/licenses/mit-license.php](http://www.opensource.org/licenses/mit-license.php)
