<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Tsetsee\SyliusQpayPlugin\Model\QPayPayment;
use Tsetsee\SyliusQpayPlugin\Payum\QPayApi;
use Tsetsee\SyliusQpayPlugin\Payum\Request\CheckPayment;

class CheckPaymentAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = QPayApi::class;
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

        /** @var QPayApi $api */
        $api = $this->api;

        $qpayInvoice = $api->getInvoice($details['invoice']['invoice_id']);
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
}
