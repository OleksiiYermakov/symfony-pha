<?php

declare(strict_types=1);

namespace App\Contract\Service;

use App\Contract\DTO\TransactionInterface;

interface CommissionCalculationInterface
{
    public function getCommissionFee(TransactionInterface $transaction): string;
}
