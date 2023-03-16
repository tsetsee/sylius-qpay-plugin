<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum\Action\API;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Reply\HttpRedirect;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Symfony\Component\Routing\RouterInterface;
use Tsetsee\SyliusQpayPlugin\Model\QPayPayment;
use Tsetsee\SyliusQpayPlugin\Payum\QPayApi;
use Tsetsee\SyliusQpayPlugin\Payum\Request\CheckPayment;

class CheckPaymentAction implements ActionInterface, ApiAwareInterface
{
    private QPayApi $api;

    public function __construct(
        private LoggerInterface $logger,
        private RouterInterface $router,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @param CheckPayment $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getFirstModel();

        $details = $payment->getDetails();

        $qpayInvoice = $this->api->getInvoice($details['invoice']['invoice_id']);

        if (true || $qpayInvoice->invoiceStatus === 'PAID') {
            $details['status'] = QPayPayment::STATE_PAID;
            $details['invoice_status'] = $qpayInvoice->toArray();

            $payment->setDetails($details);

            return;
        }

        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getFirstModel();

        throw new HttpRedirect($this->router->generate('tsetsee_qpay_plugin_payment_show', [
            'tokenValue' => $payment->getOrder()->getTokenValue(),
        ]));
    }

    /**
     * @inheritDoc
     */
    public function supports($request)
    {
        return
            $request instanceof CheckPayment &&
            $request->getFirstModel() instanceof SyliusPaymentInterface
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
