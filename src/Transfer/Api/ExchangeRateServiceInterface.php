<?php

namespace App\Transfer\Api;

use App\Transfer\Entity\Enum\CurrencyEnum;

interface ExchangeRateServiceInterface
{
    public function convert(float $amount, CurrencyEnum $fromCurrency, CurrencyEnum $toCurrency): float;
}
