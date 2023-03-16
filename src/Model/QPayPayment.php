<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Model;

enum QPayPayment: int
{
    case STATE_NEW = 0;
    case STATE_PROCESSING = 1;
    case STATE_REFUND = 2;
    case STATE_CANCEL = 3;
    case STATE_PAID = 4;
}
