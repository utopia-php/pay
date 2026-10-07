<?php

declare(strict_types=1);

namespace Utopia\Pay;

use Utopia\Pay\Payment\Options;

class Pay
{
    public function __construct(private readonly Adapter $adapter)
    {
    }

    public function purchase(int $amount, string $customerId, ?string $paymentMethodId = null, ?Options $options = null): Payment
    {
        return $this->adapter->purchase($amount, $customerId, $paymentMethodId, $options);
    }

    public function authorize(int $amount, string $customerId, ?string $paymentMethodId = null, ?Options $options = null): Payment
    {
        return $this->adapter->authorize($amount, $customerId, $paymentMethodId, $options);
    }

    public function capture(string $paymentId, ?int $amount = null, ?Options $options = null): Payment
    {
        return $this->adapter->capture($paymentId, $amount, $options);
    }

    public function cancelAuthorization(string $paymentId, ?Options $options = null): Payment
    {
        return $this->adapter->cancelAuthorization($paymentId, $options);
    }

    public function retryPurchase(string $paymentId, ?string $paymentMethodId = null, ?Options $options = null): Payment
    {
        return $this->adapter->retryPurchase($paymentId, $paymentMethodId, $options);
    }

    public function refund(string $paymentId, int $amount): Refund
    {
        return $this->adapter->refund($paymentId, $amount);
    }

    public function getPayment(string $paymentId): Payment
    {
        return $this->adapter->getPayment($paymentId);
    }

    public function updatePayment(string $paymentId, ?string $paymentMethodId = null, ?int $amount = null, ?string $currency = null, ?Options $options = null): Payment
    {
        return $this->adapter->updatePayment($paymentId, $paymentMethodId, $amount, $currency, $options);
    }

    public function deletePaymentMethod(string $paymentMethodId): bool
    {
        return $this->adapter->deletePaymentMethod($paymentMethodId);
    }

    public function createPaymentMethod(string $customerId, CardDetails $details): PaymentMethod
    {
        return $this->adapter->createPaymentMethod($customerId, $details);
    }

    public function updatePaymentMethodBillingDetails(string $paymentMethodId, ?string $name = null, ?string $email = null, ?string $phone = null, ?Address $address = null): PaymentMethod
    {
        return $this->adapter->updatePaymentMethodBillingDetails($paymentMethodId, $name, $email, $phone, $address);
    }

    public function updatePaymentMethod(string $paymentMethodId, CardDetails $details): PaymentMethod
    {
        return $this->adapter->updatePaymentMethod($paymentMethodId, $details);
    }

    public function getPaymentMethod(string $customerId, string $paymentMethodId): PaymentMethod
    {
        return $this->adapter->getPaymentMethod($customerId, $paymentMethodId);
    }

    /** @return list<PaymentMethod> */
    public function listPaymentMethods(string $customerId): array
    {
        return $this->adapter->listPaymentMethods($customerId);
    }

    /** @return list<Customer> */
    public function listCustomers(): array
    {
        return $this->adapter->listCustomers();
    }

    public function createCustomer(string $name, string $email, ?Address $address = null, ?string $paymentMethod = null): Customer
    {
        return $this->adapter->createCustomer($name, $email, $address, $paymentMethod);
    }

    public function getCustomer(string $customerId): Customer
    {
        return $this->adapter->getCustomer($customerId);
    }

    public function updateCustomer(string $customerId, string $name, string $email, ?Address $address = null, ?string $paymentMethod = null): Customer
    {
        return $this->adapter->updateCustomer($customerId, $name, $email, $address, $paymentMethod);
    }

    public function deleteCustomer(string $customerId): bool
    {
        return $this->adapter->deleteCustomer($customerId);
    }

    /** @param list<string> $paymentMethodTypes */
    public function createFuturePayment(string $customerId, ?string $paymentMethod = null, array $paymentMethodTypes = ['card'], ?CardMandate $mandate = null, ?string $paymentMethodConfiguration = null): SetupIntent
    {
        return $this->adapter->createFuturePayment($customerId, $paymentMethod, $paymentMethodTypes, $mandate, $paymentMethodConfiguration);
    }

    public function getFuturePayment(string $id): SetupIntent
    {
        return $this->adapter->getFuturePayment($id);
    }

    public function updateFuturePayment(string $id, ?string $customerId = null, ?string $paymentMethod = null, ?CardMandate $mandate = null, ?string $paymentMethodConfiguration = null): SetupIntent
    {
        return $this->adapter->updateFuturePayment($id, $customerId, $paymentMethod, $mandate, $paymentMethodConfiguration);
    }

    /** @return list<SetupIntent> */
    public function listFuturePayment(?string $customerId, ?string $paymentMethodId = null): array
    {
        return $this->adapter->listFuturePayments($customerId, $paymentMethodId);
    }

    public function getMandate(string $id): Mandate
    {
        return $this->adapter->getMandate($id);
    }

    /** @return list<Dispute> */
    public function listDisputes(?int $limit = null, ?string $paymentIntentId = null, ?string $chargeId = null, ?int $createdAfter = null): array
    {
        return $this->adapter->listDisputes($limit, $paymentIntentId, $chargeId, $createdAfter);
    }
}
