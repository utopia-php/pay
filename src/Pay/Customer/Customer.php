<?php

namespace Utopia\Pay\Customer;

use Utopia\Pay\Model;

/**
 * Customer returned by createCustomer(), getCustomer(), updateCustomer() and listCustomers()
 */
class Customer extends Model
{
    public function getId(): ?string
    {
        return $this->string('id');
    }

    public function getName(): ?string
    {
        return $this->string('name');
    }

    public function getEmail(): ?string
    {
        return $this->string('email');
    }

    public function getPhone(): ?string
    {
        return $this->string('phone');
    }

    public function getDefaultPaymentMethodId(): ?string
    {
        return $this->expandableId('invoice_settings', 'default_payment_method');
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->array('metadata') ?? [];
    }

    public function getCreatedAt(): ?int
    {
        return $this->int('created');
    }

    /**
     * getCustomer() still answers for a deleted customer, with only `id` and `deleted` set
     */
    public function isDeleted(): bool
    {
        return $this->bool('deleted') === true;
    }
}
