<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum\Action;

use GuzzleHttp\Exception\RequestException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Request\Capture;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Tsetsee\Qpay\Api\DTO\CreateInvoiceRequest;
use Tsetsee\Qpay\Api\Exception\BadResponseException;
use Tsetsee\Qpay\Api\QPayApi;
use Tsetsee\SyliusQpayPlugin\Model\QPayPayment;
use Tsetsee\SyliusQpayPlugin\Payum\SyliusApi;

final class CaptureAction implements ActionInterface, ApiAwareInterface
{
    /** @var SyliusApi */
    private $api;

    public function __construct(private LoggerInterface $logger)
    {
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getModel();
        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        $details = [
            'status' => QPayPayment::STATE_NEW,
        ];

        try {
            $client = new QPayApi(
                username: $this->api->getUsername(),
                password: $this->api->getPassword(),
                env: $this->api->getEnv(),
                options: ['logger' => $this->logger],
            );

            if ($payment->getAmount() === null) {
                $details['status'] = QPayPayment::STATE_CANCEL;
            } else {
                $invoice = $client->createInvoice(CreateInvoiceRequest::from([
                    'invoiceCode' => $this->api->getInvoiceCode(),
                    'senderInvoiceNo' => $order->getNumber(),
                    'invoiceReceiverCode' => $order->getUser()?->getUsernameCanonical() ?? $order->getNumber(),
                    'invoiceDescription' => 'purchasement of order ' . $order->getNumber(),
                    'senderBranchCode' => 'CENTRAL',
                    'amount' => $payment->getAmount() / 100.0,
                    'callbackUrl' => '',
                ]));

                $details['status'] = QPayPayment::STATE_PROCESSED;
                $details['invoice'] = $invoice->toArray();
            }
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
            $details['status'] = $response?->getStatusCode();
        } catch(BadResponseException $e) {
            $details['status'] = QPayPayment::STATE_CANCEL;
            $details['error'] = $e->getMessage();
        } finally {
            $payment->setDetails($details);
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof SyliusPaymentInterface
        ;
    }

    public function setApi($api): void
    {
        if (!$api instanceof SyliusApi) {
            throw new UnsupportedApiException('Not supported. Expected an instance of ' . SyliusApi::class);
        }

        $this->api = $api;
    }
}
