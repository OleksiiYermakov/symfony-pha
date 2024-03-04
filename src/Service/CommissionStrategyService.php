<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\DTO\CalculationStrategyInterface;
use App\Contract\Strategy\CommissionStrategyInterface;
use LogicException;

class CommissionStrategyService
{
    /** @var CommissionStrategyInterface[] */
    private array $strategies = [];

    /** @noinspection PhpUnused */
    public function addStrategy(CommissionStrategyInterface $strategy): void
    {
        $this->strategies[] = $strategy;
    }

    public function calculateCommissionFee(CalculationStrategyInterface $commission): float
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->isApplicable($commission)) {
                // Execute only the first applicable strategy
                return $strategy->calculateFee($commission);
            }
        }

        throw new LogicException('We did not find any strategy for this transaction');
    }
}
