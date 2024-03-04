<?php

declare(strict_types=1);

namespace App\Storage;

use App\Contract\DTO\TransactionInterface;
use App\Contract\Service\CurrencyExchangeInterface;
use App\Contract\Storage\TransactionStorageInterface;

class ArrayTransactionStorage implements TransactionStorageInterface
{
    /** @var TransactionInterface[] */
    private array $transactions = [];

    public function addTransaction(TransactionInterface $transaction): void
    {
        $this->transactions[] = $transaction;
    }

    /** @return TransactionInterface[] */
    public function getAll(): array
    {
        return array_values($this->transactions);
    }

    public function getUserTransactionForWeek(TransactionInterface $transaction): array
    {
        $weekStart = $transaction->getDate();
        $weekStart->modify('this week')->modify('Monday'); // Set the date to the Monday of the week
        $weekEnd = clone $transaction->getDate();
        $weekEnd->modify('Sunday'); // Get the Sunday of the week

        return array_filter(
            $this->getAll(),
            static function (TransactionInterface $t) use ($weekStart, $weekEnd, $transaction) {
                return $transaction->getUserId() === $t->getUserId()
                    && $transaction->getTransactionType() === $t->getTransactionType()
                    && $weekStart <= $t->getDate()
                    && $t->getDate() <= $weekEnd;
            },
        );
    }

    public function getUserTransactionsTotalAmountForWeek(
        TransactionInterface $transaction,
        CurrencyExchangeInterface $currencyExchange,
    ): float {
        return array_reduce(
            $this->getUserTransactionForWeek($transaction),
            static function (float $total, TransactionInterface $transaction) use ($currencyExchange): float {
                // Here we convert the transaction amount to the base currency
                $amount = $currencyExchange->convert(
                    $transaction->getAmount(),
                    $transaction->getCurrency(),
                    $currencyExchange->getBaseCurrency(),
                );

                return $total + $amount;
            },
            0,
        );
    }

    public function getUserTransactionsTotalCountForWeek(TransactionInterface $transaction): int
    {
        return count($this->getUserTransactionForWeek($transaction));
    }
}
