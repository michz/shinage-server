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

class PresentationBuilderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // always first check if the primary service is defined
        if (!$container->has('app.presentation_builder_chain')) {
            return;
        }

        $definition = $container->findDefinition('app.presentation_builder_chain');

        // find all service IDs with the app.presentation_builder tag
        $taggedServices = $container->findTaggedServiceIds('app.presentation_builder');

        foreach ($taggedServices as $id => $tags) {
            // add the transport service to the ChainTransport service
            $definition->addMethodCall('addBuilder', [new Reference($id)]);
        }
    }
}
