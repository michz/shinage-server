<?php

namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;

/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  05.11.17
 * @time     :  17:59
 */
class Builder
{
    private $factory;

    /**
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function accountMenu(/** @scrutinizer ignore-unused */ array $options)
    {
        $menu = $this->factory->createItem('root');

        $menu->setChildrenAttribute('class', 'nav navbar-nav');

        $menu->addChild('User', ['route' => 'account-edit']);
        $menu->addChild('Organizations', ['route' => 'account-organizations']);

        return $menu;
    }

    public function screenSettingsMenu(/** @scrutinizer ignore-unused */ array $options)
    {
        $menu = $this->factory->createItem('root');

        $menu->setChildrenAttribute('class', 'nav navbar-nav');

        $menu->addChild('Common', [
            'route' => 'management-screen-data',
            'routeParameters' => ['guid' => $options['guid']]
        ]);
        $menu->addChild('Schedule', [
            'route' => 'management-screen-schedule',
            'routeParameters' => ['guid' => $options['guid']]
        ]);
        $menu->addChild('Rights', [
            'route' => 'management-screen-rights',
            'routeParameters' => ['guid' => $options['guid']]
        ]);
        $menu->addChild('Offline', [
            'route' => 'management-screen-offline',
            'routeParameters' => ['guid' => $options['guid']]
        ]);

        return $menu;
    }
}
