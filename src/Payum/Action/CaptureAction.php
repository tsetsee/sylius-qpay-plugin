<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Sync;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Tsetsee\SyliusQpayPlugin\Model\QPayPayment;
use Tsetsee\SyliusQpayPlugin\Payum\Request\CreateInvoice;

/** @psalm-suppress PropertyNotSetInConstructor */
final class CaptureAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /**
         * @var Capture $request
         * @var SyliusPaymentInterface $payment
         */
        $payment = $request->getModel();

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        $token = $request->getToken();
        if ($token === null) {
            return;
        }

        $notifyToken = $this->tokenFactory->createNotifyToken(
            $token->getGatewayName(),
            $token->getDetails(),
        );

        if ($details['status'] === null) {
            $details['status'] = QPayPayment::STATE_NEW->value;
            $details['notification_url'] = $notifyToken->getTargetUrl();

            $payment->setDetails((array) $details);
            $this->gateway->execute(new CreateInvoice($request->getModel()));
        } else {
            $this->gateway->execute(new Sync($payment->getDetails()));
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof SyliusPaymentInterface
        ;
    }
}
