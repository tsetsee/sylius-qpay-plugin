<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetStatusInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Tsetsee\SyliusQpayPlugin\Model\QPayPayment;

final class StatusAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var SyliusPaymentInterface $payment */
        /** @var Generic $request */
        /** @psalm-suppress MixedAssignment, MixedMethodCall */
        $payment = $request->getFirstModel();

        /** @var array<string, mixed> $details */
        /** @psalm-suppress MixedAssignment, MixedMethodCall */
        $details = $payment->getDetails();

        /** @var int $status */
        /** @psalm-suppress MixedAssignment, MixedArrayAccess */
        $status = $details['status'];

        /** @var GetStatusInterface $request */
        if (QPayPayment::STATE_NEW->value === $status) {
            $request->markNew();

            return;
        }

        if (QPayPayment::STATE_PROCESSING->value === $status) {
            $request->markPending();

            return;
        }

        if (QPayPayment::STATE_PAID->value === $status) {
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
