<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum;

use Payum\Core\Exception\LogicException;
use Sylius\Component\Core\Model\OrderInterface;
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

class QPayApi
{
    /** @psalm-suppress PropertyNotSetInConstructor */
    private ApiQPayApi $client;

    public function __construct(
        private string $username,
        private string $password,
        private Env $env,
        private string $invoiceCode,
    ) {
    }

    /**
     * @param array<string, mixed> $options - [
     *      ?Psr\Log\LoggerInterface $logger
     * ]
     */
    public function setup(array $options): void
    {
        $this->client = new ApiQPayApi(
            username: $this->username,
            password: $this->password,
            env: $this->env,
            options: $options,
        );
    }

    public function createInvoice(
        PaymentInterface $payment,
        string $callbackURL,
    ): CreateInvoiceResponse {
        $order = $payment->getOrder();

        if ($order === null) {
            throw new LogicException('order is not null');
        }

        $amount = $payment->getAmount();
        if ($amount === null) {
            throw new LogicException('Payment amount is null');
        }

        $customer = $order->getCustomer();

        if ($customer === null) {
            throw new LogicException('Customer not found in the order');
        }

        /** @var int $customerId */
        $customerId = $customer->getId();

        return $this->client->createInvoice(CreateInvoiceRequest::from([
            'invoiceCode' => $this->invoiceCode,
            'senderInvoiceNo' => $order->getNumber(),
            'invoiceReceiverCode' => strval($customerId),
            'invoiceDescription' => 'invoice no: ' . ($order->getNumber() ?? 'no number'),
            'senderBranchCode' => 'CENTRAL',
            'amount' => $amount / 100.0,
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
