<?php

declare(strict_types=1);

namespace App\Strategy;

use App\Contract\DTO\CalculationStrategyInterface;
use App\ValueObject\TransactionType;
use App\ValueObject\UserType;

class BusinessWithdrawalCommissionStrategy extends AbstractCommissionStrategy
{
    public function __construct(protected readonly float $commissionRate)
    {
    }

    public function isApplicable(CalculationStrategyInterface $commission): bool
    {
        $transaction = $commission->getTransaction();

        return TransactionType::WITHDRAW === $transaction->getTransactionType()
            && UserType::BUSINESS === $transaction->getUserType();
    }

    public function calculateFee(CalculationStrategyInterface $commission): float
    {
        return $this->calculateFeePercentage($commission->getAmount(), $this->commissionRate);
    }
}
