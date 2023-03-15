<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum;

use Sylius\Component\Core\Model\PaymentInterface;
use Tsetsee\Qpay\Api\DTO\CheckPaymentRequest;
use Tsetsee\Qpay\Api\DTO\CheckPaymentResponse;
use Tsetsee\Qpay\Api\DTO\CreateInvoiceRequest;
use Tsetsee\Qpay\Api\DTO\CreateInvoiceResponse;
use Tsetsee\Qpay\Api\DTO\GetInvoiceResponse;
use Tsetsee\Qpay\Api\DTO\Offset;
use Tsetsee\Qpay\Api\Enum\Env;
use Tsetsee\Qpay\Api\Enum\ObjectType;
use Tsetsee\Qpay\Api\QPayApi as ApiQPayApi;

final class QPayApi
{
    private ApiQPayApi $client;

    public function __construct(
        public string $username,
        public string $password,
        public Env $env,
        public string $invoiceCode,
    ) {
    }

    public function createInvoice(
        PaymentInterface $payment,
        string $callbackURL,
    ): CreateInvoiceResponse {
        $order = $payment->getOrder();

        return $this->client->createInvoice(CreateInvoiceRequest::from([
            'invoiceCode' => $this->invoiceCode,
            'senderInvoiceNo' => $order->getNumber(),
            'invoiceReceiverCode' => (string) $order->getCustomer()->getId(),
            'invoiceDescription' => 'invoice no:' . $order->getNumber(),
            'senderBranchCode' => 'CENTRAL',
            'amount' => $payment->getAmount() / 100.0,
            // 'callbackUrl' => $this->urlGenerator->generate('payum_capture_do', [
            //     'payum_token' => $request->getToken()->getHash(),
            // ]),
            'callbackUrl' => $callbackURL,
        ]));
    }

    public function getInvoice(string $invoiceId): GetInvoiceResponse
    {
        return $this->client->getInvoice($invoiceId);
    }

    public function checkPayment(
        ObjectType $objectType,
        string $objectId,
        ?Offset $offset = null,
    ): CheckPaymentResponse {
        return $this->client->checkPayment(CheckPaymentRequest::from([
            'objectType' => $objectType->value,
            'objectId' => $objectId,
            'offset' => $offset ?? Offset::from([
                'pageNumber' => 1,
                'pageLimit' => 100,
            ]),
        ]));
    }
}
