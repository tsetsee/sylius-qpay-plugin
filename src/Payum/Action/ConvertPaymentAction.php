<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Tsetsee\SyliusQpayPlugin\Payum\QPayApi;

class ConvertPaymentAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = QPayApi::class;
    }

    /**
     * @param Convert $request
     */
    public function execute($request)
    {
        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $invoice = $this->api->createInvoice($payment, $request->getToken()->getTargetUrl());

        $request->setResult($invoice->toArray());
        // $request->setResult($payment);
    }

    public function supports($request)
    {
        return $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() === 'array'
        ;
    }
}
