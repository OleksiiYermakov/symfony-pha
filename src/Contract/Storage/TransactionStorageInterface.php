<?php

declare(strict_types=1);

namespace App\Contract\Storage;

use App\Contract\DTO\TransactionInterface;
use App\Contract\Service\CurrencyExchangeInterface;

interface TransactionStorageInterface
{
    public function addTransaction(TransactionInterface $transaction): void;

    /** @return TransactionInterface[] */
    public function getAll(): array;

    /** @return TransactionInterface[] */
    public function getUserTransactionForWeek(TransactionInterface $transaction): array;

    public function getUserTransactionsTotalAmountForWeek(
        TransactionInterface $transaction,
        CurrencyExchangeInterface $currencyExchange,
    ): float;

    public function getUserTransactionsTotalCountForWeek(TransactionInterface $transaction): int;
}
