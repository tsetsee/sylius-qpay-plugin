<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\EventListener;

use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderPaymentStates;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OrderListener
{
    public function __construct(private UrlGeneratorInterface $router)
    {
    }

    #[AsEventListener(event: 'sylius.order.qpay_show')]
    public function onShow(ResourceControllerEvent $event): void
    {
        /** @var OrderInterface $order */
        $order = $event->getSubject();

        if ($order->getPaymentState() === OrderPaymentStates::STATE_PAID) {
            $event->setResponse(new RedirectResponse(
                $this->router->generate('sylius_shop_order_thank_you'),
            ));
        }
    }
}
