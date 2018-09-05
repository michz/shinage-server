<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PresentationTypeCompilerPass implements CompilerPassInterface
{
    private const SERVICE_ID_REGISTRY = 'app.presentation.presentation_type_registry';
    private const SERVICE_TAG = 'app.presentation_type';

    public function process(ContainerBuilder $container): void
    {
        if (false === $container->has(self::SERVICE_ID_REGISTRY)) {
            return;
        }

        $definition = $container->findDefinition(self::SERVICE_ID_REGISTRY);
        $taggedServices = $container->findTaggedServiceIds(self::SERVICE_TAG);

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addType', [new Reference($id)]);
        }
    }
}
