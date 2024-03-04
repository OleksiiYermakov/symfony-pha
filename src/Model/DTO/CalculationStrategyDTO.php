<?php

declare(strict_types=1);

namespace App\Model\DTO;

use App\Contract\DTO\CalculationStrategyInterface;
use App\Contract\DTO\TransactionInterface;

readonly class CalculationStrategyDTO implements CalculationStrategyInterface
{
    public function __construct(
        private TransactionInterface $transaction,
        private float $amount,
        private float $transactionsTotalAmount,
        private int $transactionsTotalCount,
    ) {
    }

    public function getTransaction(): TransactionInterface
    {
        return $this->transaction;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getTransactionsTotalAmount(): float
    {
        return $this->transactionsTotalAmount;
    }

    public function getTransactionsTotalCount(): int
    {
        return $this->transactionsTotalCount;
    }
}
