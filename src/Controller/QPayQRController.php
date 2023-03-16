<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Controller;

use Payum\Bundle\PayumBundle\Controller\PayumController;
use Payum\Core\Model\PaymentInterface as ModelPaymentInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class QPayQRController extends PayumController
{
    public function renderQRAction(
        Request $request,
        OrderRepositoryInterface $orderRepository,
    ): Response {
        $orderId = $request->attributes->getInt('id');

        /** @var OrderInterface $order */
        $order = $orderRepository->find($orderId);

        /** @var ModelPaymentInterface $payment */
        $payment = $order->getLastPayment(PaymentInterface::STATE_PROCESSING);

        $details = $payment->getDetails();

        return $this->render('@TsetseeSyliusQpayPlugin/qr.html.twig', [
            'invoice' => $details['invoice'],
        ]);
    }

    public function callbackAction(
        Request $request,
        OrderRepositoryInterface $orderRepository,
    ): Response {
        /** @var OrderInterface $order */
        $order = $orderRepository->findOneByTokenValue((string) $request->attributes->get('payum_token'));
        $gateway = $this->payum->getGateway('QPay Payment');

        // $gateway->execute()

        return new Response('SUCCESS');
    }
}
