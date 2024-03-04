<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use App\Service\CommissionStrategyService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class LoadStrategiesCompilerPass implements CompilerPassInterface
{
    /** @psalm-suppress UnusedForeachValue */
    public function process(ContainerBuilder $container): void
    {
        $context = $container->findDefinition(CommissionStrategyService::class);
        $taggedServices = $container->findTaggedServiceIds('app.commission.fee.strategy');

        foreach ($taggedServices as $id => $service) {
            $context->addMethodCall('addStrategy', [new Reference($id)]);
        }
    }
}
