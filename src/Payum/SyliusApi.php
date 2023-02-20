<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum;

final class SyliusApi
{
    public function __construct(
        private string $invoiceCode,
    ) {
    }

     public function getInvoiceCode(): string
     {
         return $this->invoiceCode;
     }
}
