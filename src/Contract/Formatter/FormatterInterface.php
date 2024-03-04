<?php

declare(strict_types=1);

namespace App\Contract\Formatter;

interface FormatterInterface
{
    public function format(float $amount, string $currency): string;
}
