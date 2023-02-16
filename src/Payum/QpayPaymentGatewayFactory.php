<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class QpayPaymentGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'qpay_payment',
            'payum.factory_title' => 'Qpay Payment',
        ]);

        $config['payum.api'] = function (ArrayObject $config) {
            return new QpayApi($config['api_key']);
        };
    }
}
