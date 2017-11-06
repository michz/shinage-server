<?php

namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  05.11.17
 * @time     :  17:59
 */
class Builder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    private $factory;

    /**
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function accountMenu(array $options)
    {
        $menu = $this->factory->createItem('root');

        $menu->setChildrenAttribute('class', 'nav navbar-nav');

        $menu->addChild('User', array('route' => 'account-edit'));
        $menu->addChild('Organizations', array('route' => 'account-organizations'));

        return $menu;
    }
}
