<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum\Action;

use GuzzleHttp\Exception\RequestException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Request\Capture;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Tsetsee\Qpay\Api\DTO\CreateInvoiceRequest;
use Tsetsee\Qpay\Api\Exception\BadResponseException;
use Tsetsee\Qpay\Api\QPayApi;
use Tsetsee\SyliusQpayPlugin\Payum\SyliusApi;

final class CaptureAction implements ActionInterface, ApiAwareInterface
{
    /** @var SyliusApi */
    private $api;

    public function __construct(private QPayApi $client)
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
            'status' => 0,
        ];

        try {
            $invoice = $this->client->createInvoice(CreateInvoiceRequest::from([
                'invoiceCode' => $this->api->getInvoiceCode(),
                'senderInvoiceNo' => $order->getNumber(),
                'invoiceReceiverCode' => $order->getUser()?->getUsernameCanonical(),
                'invoiceDescription' => '',
                'senderBranchCode' => 'CENTRAL',
                'amount' => $payment->getAmount(),
                'callbackUrl' => '',
            ]));

            $details['invoice'] = $invoice->toArray();
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
            $details['status'] = $response?->getStatusCode();
        } catch(BadResponseException $e) {
            $details['status'] = 1;
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
