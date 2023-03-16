<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\Bundle\PayumBundle\Request\ResolveNextRoute;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ResolveNextRouteAction implements ActionInterface
{
    public function __construct(
        private UrlGeneratorInterface $router,
    ) {
    }

    /**
     * @param ResolveNextRoute $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getFirstModel();

        if ($payment->getState() === SyliusPaymentInterface::STATE_COMPLETED) {
            $request->setRouteName('sylius_shop_order_show');
            $request->setRouteParameters([
                'tokenValue' => $payment->getOrder()->getTokenValue(),
            ]);

            return;
        }

        $request->setRouteName('tsetsee_qpay_plugin_payment_show');
        $request->setRouteParameters([
            'tokenValue' => $payment->getOrder()->getTokenValue(),
        ]);
    }

    public function supports($request): bool
    {
        return
            $request instanceof ResolveNextRoute &&
            $request->getFirstModel() instanceof SyliusPaymentInterface
        ;
    }
}
