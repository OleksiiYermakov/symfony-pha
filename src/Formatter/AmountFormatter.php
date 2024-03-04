<?php

declare(strict_types=1);

namespace App\Formatter;

use App\Contract\Formatter\FormatterInterface;

readonly class AmountFormatter implements FormatterInterface
{
    public function __construct(
        private array $currencyWithoutCents = [],
    ) {
    }

    public function format(float $amount, string $currency): string
    {
        // Decimal rule and precision rule for currency
        $formatRule = !in_array($currency, $this->currencyWithoutCents, true);
        // If currency with decimal cents used round() and if without used ceil()
        $amount = $formatRule ? round($amount, 3) : ceil($amount);

        return number_format(
            $amount,
            $formatRule ? 2 : 0, // Use 2 decimal places if currency uses cents, otherwise 0
            '.',
            '',
        );
    }
}
