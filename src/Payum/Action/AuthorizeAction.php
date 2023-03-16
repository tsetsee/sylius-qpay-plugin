<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum\Action;

use GuzzleHttp\Exception\RequestException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Generic;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Symfony\Component\Routing\RouterInterface;
use Tsetsee\Qpay\Api\Exception\BadResponseException;
use Tsetsee\SyliusQpayPlugin\Model\QPayPayment;
use Tsetsee\SyliusQpayPlugin\Payum\QPayApi;

final class AuthorizeAction implements ActionInterface, ApiAwareInterface
{
    private QPayApi $api;

    public function __construct(
        private LoggerInterface $logger,
        private RouterInterface $router,
    ) {
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var Generic $request */
        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getModel();
        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        $details = [
            'status' => QPayPayment::STATE_NEW,
        ];

        try {
            if ($payment->getState() === PaymentInterface::STATE_NEW) {
                if ($payment->getAmount() === null) {
                    $details['status'] = QPayPayment::STATE_CANCEL;
                } else {
                    $token = $request->getToken();

                    $targetURL = $this->router->generate('payum_notify_do', [
                            'payum_token' => $token->getHash(),
                        ], RouterInterface::ABSOLUTE_URL);

                    $invoice = $this->api->createInvoice(
                        $payment,
                        $targetURL,
                    );

                    $details['status'] = QPayPayment::STATE_NEW;
                    $details['invoice'] = $invoice->toArray();
                    $details['notify_url'] = $targetURL;
                }
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
            $request instanceof Authorize &&
            $request->getModel() instanceof SyliusPaymentInterface
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
