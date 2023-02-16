<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Payum;

final class QpayApi
{
    /** @var string */
    private $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }
}
