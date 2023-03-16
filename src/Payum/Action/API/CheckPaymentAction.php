<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum\Action\API;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Psr\Log\LoggerInterface;
use Tsetsee\SyliusQpayPlugin\Model\QPayPayment;
use Tsetsee\SyliusQpayPlugin\Payum\QPayApi;
use Tsetsee\SyliusQpayPlugin\Payum\Request\CheckPayment;

class CheckPaymentAction implements ActionInterface, ApiAwareInterface
{
    private QPayApi $api;

    public function __construct(
        private LoggerInterface $logger,
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

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $qpayInvoice = $this->api->getInvoice($details['invoice']['invoice_id']);
        dd($qpayInvoice);

        // if ($invoice->invoiceStatus === Invoice) {
        //     $details->replace([
        //         'status' => QPayPayment::STATE_PAID,
        //         'invoice' => $invoice->toArray(),
        //     ]);
        // }
    }

    /**
     * @inheritDoc
     */
    public function supports($request)
    {
        return
            $request instanceof CheckPayment &&
            $request->getModel() instanceof \ArrayAccess
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
