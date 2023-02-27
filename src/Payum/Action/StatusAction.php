<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;

final class StatusAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var GetStatusInterface $request */
        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getFirstModel();

        $details = $payment->getDetails();

        if (0 === $details['status']) {
            $request->markAuthorized();

            return;
        }

        $request->markFailed();
    }

    public function supports($request): bool
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getFirstModel() instanceof SyliusPaymentInterface
        ;
    }
}
