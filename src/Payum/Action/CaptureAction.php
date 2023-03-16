<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Generic;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tsetsee\SyliusQpayPlugin\Model\QPayPayment;
use Tsetsee\SyliusQpayPlugin\Payum\QPayApi;
use Tsetsee\SyliusQpayPlugin\Payum\Request\CheckPayment;
use Tsetsee\SyliusQpayPlugin\Payum\Request\CreateInvoice;

final class CaptureAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    private QPayApi $api;

    public function __construct(
        private LoggerInterface $logger,
        private UrlGeneratorInterface $router,
    ) {
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var Generic $request */
        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getModel();

        $details = $payment->getDetails();

        if (!isset($details['status'])) {
            $this->gateway->execute(new CreateInvoice($request->getToken()));
        } elseif ($details['status'] === QPayPayment::STATE_PROCESSING->value) {
            $this->gateway->execute(new CheckPayment($request->getToken()));
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof SyliusPaymentInterface
        ;
    }

    /**
     * @inheritdoc
     */
    public function setApi($api)
    {
        if (!$api instanceof QPayApi) {
            throw new UnsupportedApiException(sprintf('Not supported api given. It must be an instance of %s', QPayApi::class));
        }

        $api->setup([
            'logger' => $this->logger,
        ]);

        $this->api = $api;
    }
}
