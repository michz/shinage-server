<?php

namespace AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  27.10.17
 * @time     :  12:03
 */
class PresentationBuilderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
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
            $definition->addMethodCall('addBuilder', array(new Reference($id)));
        }
    }
}
