<?php

declare(strict_types=1);

namespace App\Contract\DTO;

interface CalculationStrategyInterface
{
    public function getTransaction(): TransactionInterface;

    public function getAmount(): float;

    public function getTransactionsTotalAmount(): float;

    public function getTransactionsTotalCount(): int;
}
