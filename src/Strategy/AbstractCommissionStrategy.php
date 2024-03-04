<?php

declare(strict_types=1);

namespace App\Strategy;

use App\Contract\Strategy\CommissionStrategyInterface;

abstract class AbstractCommissionStrategy implements CommissionStrategyInterface
{
    public function calculateFeePercentage(float $amount, float $commissionFee): float
    {
        return $amount * $commissionFee;
    }
}
