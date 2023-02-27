<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum;

use Tsetsee\Qpay\Api\Enum\Env;

final class SyliusApi
{
    public function __construct(
        private string $username,
        private string $password,
        private Env $env,
        private string $invoiceCode,
    ) {
    }

     public function getInvoiceCode(): string
     {
         return $this->invoiceCode;
     }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getEnv(): Env
    {
        return $this->env;
    }
}
