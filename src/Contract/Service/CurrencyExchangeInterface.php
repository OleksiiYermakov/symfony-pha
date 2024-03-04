<?php

declare(strict_types=1);

namespace App\Contract\Service;

interface CurrencyExchangeInterface
{
    public function getBaseCurrency(): string;

    public function convert(float $amount, string $fromCurrency, string $toCurrency): float;
}
