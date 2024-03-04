<?php

declare(strict_types=1);

namespace App\Strategy;

use App\Contract\DTO\CalculationStrategyInterface;
use App\ValueObject\TransactionType;
use App\ValueObject\UserType;

class PrivateWithdrawalCommissionStrategy extends AbstractCommissionStrategy
{
    public function __construct(
        protected readonly float $commissionRate,
        protected readonly float $weeklyFreeAmount,
        protected readonly int $weeklyFreeCount,
    ) {
    }

    public function isApplicable(CalculationStrategyInterface $commission): bool
    {
        $transaction = $commission->getTransaction();

        return TransactionType::WITHDRAW === $transaction->getTransactionType()
            && UserType::PRIVATE === $transaction->getUserType();
    }

    public function calculateFee(CalculationStrategyInterface $commission): float
    {
        $commissionRate = 0;
        $withdrawalAmount = $commission->getAmount();
        $transactionsTotalCount = $commission->getTransactionsTotalCount();
        $transactionsTotalAmount = $commission->getTransactionsTotalAmount();
        // Calculate the exceeded amount for this transaction and transactions during this week
        $transactionExceededAmount = max(0, $withdrawalAmount - $this->weeklyFreeAmount);
        $weeklyExceededAmount = max(0, $transactionsTotalAmount - $this->weeklyFreeAmount);

        //  Check if current transaction exceeded amount
        if ($transactionExceededAmount > 0) {
            $commissionRate = $this->commissionRate;
            $withdrawalAmount -= $this->weeklyFreeAmount;
            // Check if exceeded weekly free amount or count of transactions
        } elseif ($weeklyExceededAmount > 0 || $transactionsTotalCount >= 3) {
            $commissionRate = $this->commissionRate;
            // Check if amount + transaction total amount exceeds the weekly free amount
        } elseif ($withdrawalAmount + $transactionsTotalAmount > $this->weeklyFreeAmount) {
            $commissionRate = $this->commissionRate;
            $withdrawalAmount -= abs($transactionsTotalAmount - $this->weeklyFreeAmount);
        }

        // Calculate fee based on commission rate
        return $this->calculateFeePercentage($withdrawalAmount, $commissionRate);
    }
}
