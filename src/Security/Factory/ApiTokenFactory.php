<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Security\Factory;

use App\Security\Authentication\Provider\ApiTokenProvider;
use App\Security\Firewall\ApiUserListener;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ApiTokenFactory implements SecurityFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.apitoken.' . $id;
        $container
            ->setDefinition($providerId, new ChildDefinition(ApiTokenProvider::class))
            ->setArgument(0, new Reference($userProvider));

        $listenerId = 'security.authentication.listener.apitoken.' . $id;
        $container->setDefinition($listenerId, new ChildDefinition(ApiUserListener::class));

        return [$providerId, $listenerId, $defaultEntryPoint];
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'apitoken';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(NodeDefinition $node)
    {
    }
}
