# Adding a new payment adapter 💾

This document is part of the Utopia contributors' guide. Before you continue reading this document make sure you have read the [Code of Conduct](../../CODE_OF_CONDUCT.md) and the [Contributing Guide](../../CONTRIBUTING.md).

## Getting started

Payment adapters help developers process payments using payment providers. By adding a new payment provider, you will be able to use it to process payments.

Utopia is and will always be tech-agnostic, meaning we are creating tools based on technologies you already use and love instead of creating a new toolset for you. With that in mind, we accept all contributions with adapters for any payment provider.

## 1. Prerequisites

It's really easy to contribute to an open source project, but when using GitHub, there are a few steps we need to follow. This section will take you step-by-step through the process of preparing your own local version of utopia-php/pay, where you can make any changes without affecting Utopia right away.

> If you are experienced with GitHub or have made a pull request before, you can skip to [Implement new adapter](#2-implement-new-adapter).

### 1.1 Fork the utopia-php/pay repository

Before making any changes, you will need to fork Utopia's repository to keep branches on the official repo clean. To do that, visit the [utopia/pay Github repository](https://github.com/utopia-php/pay) and click on the fork button.

This will redirect you from `github.com/utopia-php/pay` to `github.com/YOUR_USERNAME/pay`, meaning all changes you do will only be done inside your repository. Once you are there, click the highlighted `Code` button, copy the URL, and clone the repository to your computer using `git clone` command:

```shell
$ git clone [COPIED_URL]
```

> To clone a repository, you will need a basic understanding of CLI and git-cli binaries installed. If you are a beginner, we recommend you to use `Github Desktop`. It is a really clean and simple visual Git client.

Finally, you will need to create a `feat-ZZZ-adapter` branch based on the `main` branch and switch to it. Replace `ZZZ` with the adapter name.

## 2. Implement new adapter

### 2.1 Add adapter class

Before implementing the adapter, please make sure to **not use any PHP library!** You will need to build app API calls using HTTP requests.

Create a new file `XXX.php` where `XXX` is the name of the adapter in [`PascalCase`](https://stackoverflow.com/a/41769355/7659504) in this location
```bash
src/Pay/Adapter/XXX.php
```

Inside this file, create a new class that extends adapter abstract class `Adapter`. Note that the class name should start with a capital letter, as PHP FIG standards suggest.

Once a new class is created, you can start to implement your new adapter's flow. We have prepared a starting point for adapter class below, but you should also consider looking at other adapter implementations and try to follow the same standards.

```php
<?php

namespace Utopia\Pay\Adapter;

use Utopia\Pay\Adapter;

class [PROVIDER_NAME] extends Adapter
{


    /**
     * Get name of the payment gateway
     */
    public function getName(): string
    {
        return '[PROVIDER_VERBOSE_UNIQUE_NAME]';
    }

    /**
     * Make a purchase request
     */
    public function purchase(int $amount, string $customerId, string $paymentMethodId = null, array $additionalParams = []): array
    {
        
    }

    /**
     * Refund payment
     */
    public function refund(string $paymentId, int $amount = null, string $reason = null): array
    {

    }

    /**
     * Add a credit card for customer
     */
    public function createPaymentMethod(string $customerId, string $type, array $paymentMethodDetails): array
    {
        
    }

    /**
     * List cards
     */
    public function listPaymentMethods(string $customerId): array
    {
        
    }

    /**
     * List Customer Payment Methods
     */
    public function getPaymentMethod(string $customerId, string $paymentMethodId): array
    {
        
    }

    /**
     * Update card
     */
    public function updatePaymentMethodBillingDetails(string $paymentMethodId, string $name = null, string $email = null, string $phone = null, array $address = null): array
    {
        
    }

    public function updatePaymentMethod(string $paymentMethodId, string $type, array $details): array
    {
        
    }

    /**
     * Delete a credit card record
     */
    public function deletePaymentMethod(string $paymentMethodId): bool
    {
        
    }

    /**
     * Add new customer in the gateway database
     * returns the newly created customer
     *
     * @throws Exception
     */
    public function createCustomer(string $name, string $email, array $address = [], string $paymentMethod = null): array
    {
        
    }

    /**
     * List customers
     */
    public function listCustomers(): array
    {
        
    }

    /**
     * Get customer details by ID
     */
    public function getCustomer(string $customerId): array
    {
        
    }

    /**
     * Update customer details
     */
    public function updateCustomer(string $customerId, string $name, string $email, array $address = null, string $paymentMethod = null): array
    {
        
    }

    /**
     * Delete customer by ID
     */
    public function deleteCustomer(string $customerId): bool
    {
        
    }

    /**
     * Save Payment Method from Front-end
     */
    public function createFuturePayment(string $customerId, array $paymentMethodTypes): array
    {
        
    }
}

```

> If you copy this template, make sure to replace all placeholders wrapped like `[THIS]` and to implement everything marked as `TODO:`.

When implementing new adapter, please make sure to follow these rules:

- `getName()` needs to use same name as file name with first letter lowercased. For example, in `Stripe.php`, we use `stripe`


Please mention in your documentation what resources or API docs you used to implement the provider's API.

## 2. Test your adapter

After you finished adding your new adapter, you should write a proper test for it. To do that, you create `tests/Pay/Adapter/[PROVIDER_NAME]Tests.php` and write tests for the provider. Look at the test written for the existing adapters for examples.

To run the test, you can simply run

```bash
composer test
```

## 4. Raise a pull request

First of all, commit the changes with the message `Add XXX Adapter` (where `XXX` is adapter name) and push it. This will publish a new branch to your forked version of utopia-php/pay. If you visit `github.com/YOUR_USERNAME/pay`, you will see a new alert saying you are ready to submit a pull request. Follow the steps GitHub provides, and at the end, you will have your pull request submitted.

## 🤕 Stuck ?
If you need any help with the contribution, feel free to head over to [Appwrite discord channel](https://appwrite.io/discord) and we'll be happy to help you out.
