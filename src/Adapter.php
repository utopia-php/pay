<?php

declare(strict_types=1);

namespace Utopia\Pay;

use Utopia\Pay\Payment\Options;
use Utopia\Pay\Refund\Reason;

abstract class Adapter
{
    abstract public function purchase(int $amount, string $customerId, ?string $paymentMethodId = null, ?Options $options = null): Payment;

    abstract public function authorize(int $amount, string $customerId, ?string $paymentMethodId = null, ?Options $options = null): Payment;

    abstract public function capture(string $paymentId, ?int $amount = null, ?Options $options = null): Payment;

    abstract public function cancelAuthorization(string $paymentId, ?Options $options = null): Payment;

    abstract public function updatePayment(string $paymentId, ?string $paymentMethodId = null, ?int $amount = null, ?string $currency = null, ?Options $options = null): Payment;

    abstract public function retryPurchase(string $paymentId, ?string $paymentMethodId = null, ?Options $options = null): Payment;

    abstract public function refund(string $paymentId, ?int $amount = null, ?Reason $reason = null): Refund;

    abstract public function getPayment(string $paymentId): Payment;

    abstract public function createPaymentMethod(string $customerId, CardDetails $details): PaymentMethod;

    abstract public function updatePaymentMethodBillingDetails(string $paymentMethodId, ?string $name = null, ?string $email = null, ?string $phone = null, ?Address $address = null): PaymentMethod;

    abstract public function updatePaymentMethod(string $paymentMethodId, CardDetails $details): PaymentMethod;

    /** @return list<PaymentMethod> */
    abstract public function listPaymentMethods(string $customerId): array;

    abstract public function deletePaymentMethod(string $paymentMethodId): bool;

    abstract public function createCustomer(string $name, string $email, ?Address $address = null, ?string $paymentMethod = null): Customer;

    /** @return list<Customer> */
    abstract public function listCustomers(): array;

    abstract public function getCustomer(string $customerId): Customer;

    abstract public function updateCustomer(string $customerId, string $name, string $email, ?Address $address = null, ?string $paymentMethod = null): Customer;

    abstract public function deleteCustomer(string $customerId): bool;

    abstract public function getPaymentMethod(string $customerId, string $paymentMethodId): PaymentMethod;

    /** @param list<string> $paymentMethodTypes */
    abstract public function createFuturePayment(string $customerId, ?string $paymentMethod = null, array $paymentMethodTypes = [], ?CardMandate $mandate = null, ?string $paymentMethodConfiguration = null): SetupIntent;

    /** @return list<SetupIntent> */
    abstract public function listFuturePayments(?string $customerId = null, ?string $paymentMethodId = null): array;

    abstract public function getFuturePayment(string $id): SetupIntent;

    abstract public function updateFuturePayment(string $id, ?string $customerId = null, ?string $paymentMethod = null, ?CardMandate $mandate = null, ?string $paymentMethodConfiguration = null): SetupIntent;

    abstract public function getMandate(string $id): Mandate;

    /** @return list<Dispute> */
    abstract public function listDisputes(?int $limit = null, ?string $paymentIntentId = null, ?string $chargeId = null, ?int $createdAfter = null): array;
}
