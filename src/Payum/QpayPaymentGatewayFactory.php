<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\Core\GatewayFactoryInterface;
use Psr\Log\LoggerInterface;
use Tsetsee\Qpay\Api\Enum\Env;

final class QpayPaymentGatewayFactory extends GatewayFactory
{
    /**
     * @param array<string, mixed> $defaultConfig
     * @param GatewayFactoryInterface $coreGatewayFactory
     */
    public function __construct(
        array $defaultConfig = [],
        GatewayFactoryInterface $coreGatewayFactory = null,
        // private LoggerInterface $logger,
    ) {
        parent::__construct($defaultConfig, $coreGatewayFactory);
    }

    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'qpay',
            'payum.factory_title' => 'Qpay Payment',
        ]);

        $config['payum.api'] = function (ArrayObject $config) {
            return new QPayApi(
                username: strval($config['username']),
                password: strval($config['password']),
                env: Env::from(strval($config['env'])),
                invoiceCode: strval($config['invoiceCode']),
            );
        };
    }
}
