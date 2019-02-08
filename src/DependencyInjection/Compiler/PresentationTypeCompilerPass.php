<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\DependencyInjection\Compiler;

use App\Presentation\PresentationTypeRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PresentationTypeCompilerPass implements CompilerPassInterface
{
    private const SERVICE_TAG = 'app.presentation_type';

    public function process(ContainerBuilder $container): void
    {
        if (false === $container->has(PresentationTypeRegistry::class)) {
            return;
        }

        $definition = $container->findDefinition(PresentationTypeRegistry::class);
        $taggedServices = $container->findTaggedServiceIds(self::SERVICE_TAG);

        foreach (array_keys($taggedServices) as $id) {
            $definition->addMethodCall('addPresentationType', [new Reference($id)]);
        }
    }
}
