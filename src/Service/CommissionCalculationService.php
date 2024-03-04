<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\DTO\TransactionInterface;
use App\Contract\Formatter\FormatterInterface;
use App\Contract\Service\CommissionCalculationInterface;
use App\Contract\Service\CurrencyExchangeInterface;
use App\Contract\Storage\TransactionStorageInterface;
use App\Model\DTO\CalculationStrategyDTO;

readonly class CommissionCalculationService implements CommissionCalculationInterface
{
    public function __construct(
        private TransactionStorageInterface $transactionStorage,
        private CommissionStrategyService $commissionFeeStrategyService,
        private CurrencyExchangeInterface $currencyExchangeService,
        private FormatterInterface $numberFormatter,
    ) {
    }

    public function getCommissionFee(TransactionInterface $transaction): string
    {
        // Calculate the total amount in base currency for a week
        $transactionsTotalAmount = $this->transactionStorage->getUserTransactionsTotalAmountForWeek(
            $transaction,
            $this->currencyExchangeService
        );
        // Calculate the total transaction count for a week
        $transactionsTotalCount = $this->transactionStorage->getUserTransactionsTotalCountForWeek($transaction);
        // Convert transaction amount to base currency
        $convertedAmount = $this->currencyExchangeService->convert(
            $transaction->getAmount(),
            $transaction->getCurrency(),
            $this->currencyExchangeService->getBaseCurrency(),
        );
        // Creating DTO to be used in commission fee calculation strategies
        $commissionDTO = new CalculationStrategyDTO(
            transaction: $transaction,
            amount: $convertedAmount,
            transactionsTotalAmount: $transactionsTotalAmount,
            transactionsTotalCount: $transactionsTotalCount,
        );
        // Calculate commission fee
        $commissionFee = $this->commissionFeeStrategyService->calculateCommissionFee($commissionDTO);
        // Exchange the commission fee from the base currency to the transaction's currency
        $exchangedCommissionFee = $this->currencyExchangeService->convert(
            $commissionFee,
            $this->currencyExchangeService->getBaseCurrency(),
            $transaction->getCurrency(),
        );
        // Add transaction to history
        $this->transactionStorage->addTransaction($transaction);

        return $this->numberFormatter->format($exchangedCommissionFee, $transaction->getCurrency());
    }
}
