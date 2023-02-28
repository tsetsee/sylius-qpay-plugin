<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Controller;

use Payum\Core\Model\PaymentInterface as ModelPaymentInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tsetsee\Qpay\Api\DTO\CreateInvoiceResponse;

final class QPayQRController extends AbstractController
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function renderQRAction(Request $request): Response
    {
        $orderId = $request->attributes->getInt('id');

        /** @var OrderInterface $order */
        $order = $this->orderRepository->find($orderId);

        /** @var ModelPaymentInterface $payment */
        $payment = $order->getLastPayment(PaymentInterface::STATE_PROCESSING);

        $details = $payment->getDetails();

        $invoice = CreateInvoiceResponse::from($details['invoice']);

        return $this->render('@TsetseeSyliusQpayPlugin/qr.html.twig', [
            'invoice' => $invoice,
        ]);
    }
}
