<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Notify;
use Tsetsee\SyliusQpayPlugin\Payum\Request\CheckPayment;

final class NotifyAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @inheritDoc
     *
     * @param $request Notify
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $this->gateway->execute(new CheckPayment($request->getFirstModel()));

        throw new HttpResponse('SUCCESS', 200);
    }

    /**
     * @inheritDoc
     */
    public function supports($request)
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
