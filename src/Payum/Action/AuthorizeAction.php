<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Request\Authorize;
use Sylius\Component\Core\Model\PaymentInterface;
use Tsetsee\SyliusQpayPlugin\Payum\QPayApi;

class AuthorizeAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = QPayApi::class;
    }

    public function execute($request)
    {
        /** @var PaymentInterface */
        $payment = $request->getFirstModel();

        /** @var QPayApi $api */
        $api = $this->api;

        $invoiceDetail = $api->getInvoice($payment->getDetails()['invoiceId']);

        dd($invoiceDetail);
        $payment->setDetails();
    }

    public function supports($request)
    {
        return $request instanceof Authorize &&
            $request->getFirstModel() instanceof PaymentInterface;
    }
}
