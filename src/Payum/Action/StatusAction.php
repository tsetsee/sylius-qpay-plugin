<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetStatusInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Symfony\Component\Routing\RouterInterface;
use Tsetsee\SyliusQpayPlugin\Model\QPayPayment;

final class StatusAction implements ActionInterface
{
    public function __construct(private RouterInterface $router)
    {
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var Generic $request */
        $token = $request->getToken();

        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getFirstModel();

        $details = $payment->getDetails();

        /** @var GetStatusInterface $request */
        if (QPayPayment::STATE_PROCESSED->value === $details['status']) {
            $request->markPending();

            throw new HttpRedirect($this->router->generate('tsetsee_qpay_plugin_payment_show', [
                'tokenValue' => $payment->getOrder()->getTokenValue(),
            ]));

            return;
        }

        $request->markFailed();
    }

    public function supports($request): bool
    {
        /** @var Generic $request */
        return
            $request instanceof GetStatusInterface &&
            $request->getFirstModel() instanceof SyliusPaymentInterface
        ;
    }
}
