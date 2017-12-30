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

        $menu->addChild('User', array('route' => 'account-edit'));
        $menu->addChild('Organizations', array('route' => 'account-organizations'));

        return $menu;
    }
}
