<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum\Action\API;

use GuzzleHttp\Exception\RequestException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Tsetsee\Qpay\Api\Exception\BadResponseException;
use Tsetsee\SyliusQpayPlugin\Model\QPayPayment;
use Tsetsee\SyliusQpayPlugin\Payum\QPayApi;
use Tsetsee\SyliusQpayPlugin\Payum\Request\CreateInvoice;

final class CreateInvoiceAction implements ActionInterface, ApiAwareInterface
{
    /** @psalm-suppress PropertyNotSetInConstructor */
    private QPayApi $api;

    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /**
         * @var SyliusPaymentInterface $payment
         * @var CreateInvoice $request
         */
        $payment = $request->getModel();

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        /** @var ?int $status */
        $status = $details['status'];

        if ($status !== QPayPayment::STATE_NEW->value) {
            throw new LogicException('invalid status code: ' . (string) $status);
        }

        try {
            if ($payment->getAmount() === null) {
                $details['status'] = QPayPayment::STATE_CANCEL->value;

                return;
            }

            $order = $payment->getOrder();

            if ($order === null) {
                throw new LogicException('Order not found');
            }

            $customer = $order->getCustomer();

            if ($customer === null) {
                throw new LogicException('Customer not found in the order');
            }

            $amount = $payment->getAmount();

            if ($amount == null) {
                throw new LogicException('amount is null');
            }

            /** @var int $customerId */
            $customerId = $customer->getId();

            $invoice = $this->api->createInvoice([
                'senderInvoiceNo' => $order->getNumber(),
                'invoiceReceiverCode' => (string) $customerId,
                'invoiceDescription' => 'invoice no: ' . ($order->getNumber() ?? 'no number'),
                'senderBranchCode' => 'CENTRAL',
                'amount' => $amount / 100.0,
                'callbackUrl' => $details['notification_url'],
            ]);

            $details['status'] = QPayPayment::STATE_PROCESSING->value;
            $details['invoice'] = (array) $invoice->toArray();
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
            $details['status'] = $response?->getStatusCode();
        } catch(BadResponseException $e) {
            $details['status'] = QPayPayment::STATE_CANCEL->value;
            $details['error'] = $e->getMessage();
        }

        $payment->setDetails((array) $details);
    }

    public function supports($request): bool
    {
        return
            $request instanceof CreateInvoice &&
            $request->getModel() instanceof SyliusPaymentInterface
        ;
    }

    /**
     * @inheritdoc
     */
    public function setApi($api): void
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
