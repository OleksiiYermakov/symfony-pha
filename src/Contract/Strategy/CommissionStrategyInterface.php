<?php

declare(strict_types=1);

namespace App\Contract\Strategy;

use App\Contract\DTO\CalculationStrategyInterface;

interface CommissionStrategyInterface
{
    public function isApplicable(CalculationStrategyInterface $commission): bool;

    public function calculateFee(CalculationStrategyInterface $commission): float;

    public function calculateFeePercentage(float $amount, float $commissionFee): float;
}
