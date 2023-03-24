<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum\Action\API;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Psr\Log\LoggerInterface;
use Tsetsee\SyliusQpayPlugin\Model\QPayPayment;
use Tsetsee\SyliusQpayPlugin\Payum\QPayApi;
use Tsetsee\SyliusQpayPlugin\Payum\Request\CheckPayment;

class CheckPaymentAction implements ActionInterface, ApiAwareInterface
{
    /** @psalm-suppress PropertyNotSetInConstructor */
    private QPayApi $api;

    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var CheckPayment $request */
        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (!isset($details['invoice'])) {
            throw new LogicException('bad invoice array');
        }

        /** @var array<string, mixed> $invoice */
        $invoice = $details['invoice'];

        /** @var ?string $invoiceId */
        $invoiceId = $invoice['invoice_id'] ?? null;

        if ($invoiceId === null) {
            throw new LogicException('invoice_id field not found in invoice array');
        }

        $qpayInvoice = $this->api->getInvoice($invoiceId);

        /** @psalm-suppress MixedAssignment */
        $details['invoice_details'] = $qpayInvoice->toArray();

        if ($qpayInvoice->invoiceStatus === 'CLOSED') {
            $details['status'] = QPayPayment::STATE_PAID->value;
        }
    }

    /**
     * @inheritDoc
     */
    public function supports($request)
    {
        return
            $request instanceof CheckPayment &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }

    /**
     * @inheritDoc
     */
    public function setApi($api): void
    {
        if (!$api instanceof QPayApi) {
            throw new UnsupportedApiException(sprintf('Not supported api given. It must be an instance of %s', QPayApi::class));
        }

        $api->setup([
            'logger' => $this->logger,
        ]);

        $this->api = $api;
    }
}
