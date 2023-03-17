<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetStatusInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Symfony\Component\Routing\RouterInterface;
use Tsetsee\SyliusQpayPlugin\Model\QPayPayment;

final class StatusAction implements ActionInterface
{
    public function __construct()
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
        // if ($payment->getState() === PaymentInterface::STATE_NEW) {
        //     $request->markPending();
        //
        //     return;
        // }

        if (QPayPayment::STATE_NEW->value === $details['status']) {
            $request->markNew();

            return;
        }

        if (QPayPayment::STATE_PROCESSING->value === $details['status']) {
            $request->markPending();

            return;
        }

        if (QPayPayment::STATE_PAID->value === $details['status']) {
            $request->markCaptured();

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
