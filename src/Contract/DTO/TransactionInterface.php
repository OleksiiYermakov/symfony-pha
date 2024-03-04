<?php

declare(strict_types=1);

namespace App\Contract\DTO;

use DateTime;

interface TransactionInterface
{
    public function getDate(): DateTime;

    public function getUserId(): int;

    public function getUserType(): string;

    public function getTransactionType(): string;

    public function getAmount(): float;

    public function getCurrency(): string;
}
