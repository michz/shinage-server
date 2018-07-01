<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

class Builder
{
    /** @var FactoryInterface */
    private $factory;

    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param mixed[]|array $options
     */
    public function accountMenu(/* @scrutinizer ignore-unused */ array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        $menu->setChildrenAttribute('class', 'nav navbar-nav');

        $menu->addChild('User', ['route' => 'account-edit']);
        $menu->addChild('Organizations', ['route' => 'account-organizations']);

        return $menu;
    }

    /**
     * @param mixed[]|array $options
     */
    public function screenSettingsMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        $menu->setChildrenAttribute('class', 'nav navbar-nav');

        $menu->addChild('Common', [
            'route' => 'management-screen-data',
            'routeParameters' => ['guid' => $options['guid']],
        ]);
        $menu->addChild('Schedule', [
            'route' => 'management-screen-schedule',
            'routeParameters' => ['guid' => $options['guid']],
        ]);
        $menu->addChild('Rights', [
            'route' => 'management-screen-rights',
            'routeParameters' => ['guid' => $options['guid']],
        ]);
        $menu->addChild('Offline', [
            'route' => 'management-screen-offline',
            'routeParameters' => ['guid' => $options['guid']],
        ]);

        return $menu;
    }
}
