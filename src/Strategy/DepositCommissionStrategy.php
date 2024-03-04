<?php

declare(strict_types=1);

namespace App\Strategy;

use App\Contract\DTO\CalculationStrategyInterface;
use App\ValueObject\TransactionType;

/**
 * This strategy is global deposit strategy for business and private user type.
 */
class DepositCommissionStrategy extends AbstractCommissionStrategy
{
    public function __construct(protected readonly float $commissionRate)
    {
    }

    public function isApplicable(CalculationStrategyInterface $commission): bool
    {
        return TransactionType::DEPOSIT === $commission->getTransaction()->getTransactionType();
    }

    public function calculateFee(CalculationStrategyInterface $commission): float
    {
        return $this->calculateFeePercentage($commission->getAmount(), $this->commissionRate);
    }
}
