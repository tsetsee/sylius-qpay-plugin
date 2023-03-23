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

/** @psalm-suppress PropertyNotSetInConstructor */
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
        $payment = $order->getLastPayment(PaymentInterface::STATE_NEW);

        $details = $payment->getDetails();

        return $this->render('@TsetseeSyliusQpayPlugin/qr.html.twig', [
            'invoice' => $details['invoice'],
        ]);
    }
}
