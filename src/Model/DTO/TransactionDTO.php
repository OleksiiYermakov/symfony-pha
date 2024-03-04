<?php

declare(strict_types=1);

namespace App\Model\DTO;

use App\Contract\DTO\TransactionInterface;
use DateTime;

readonly class TransactionDTO implements TransactionInterface
{
    public function __construct(
        private DateTime $date,
        private int $userId,
        private string $userType,
        private string $transactionType,
        private float $amount,
        private string $currency,
    ) {
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getUserType(): string
    {
        return $this->userType;
    }

    public function getTransactionType(): string
    {
        return $this->transactionType;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }
}
