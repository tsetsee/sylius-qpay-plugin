<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Tsetsee\Qpay\Api\Enum\Env;
use Tsetsee\SyliusQpayPlugin\Payum\Action\StatusAction;

final class QpayPaymentGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'qpay',
            'payum.factory_title' => 'Qpay Payment',
            // 'payum.action.status' => new StatusAction(),
        ]);

        $config['payum.api'] = function (ArrayObject $config) {
            return new QPayApi(
                username: (string) ($config['username']),
                password: (string) $config['password'],
                env: Env::from($config['env']),
                invoiceCode: (string) $config['invoiceCode'],
            );
        };
    }
}
