<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Menu;

use App\Entity\Screen;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

readonly class Builder
{
    public function __construct(
        private FactoryInterface $factory,
        private AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    /**
     * @param mixed[]|array $options
     */
    public function accountMenu(/* @scrutinizer ignore-unused */ array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        $menu->setChildrenAttribute('class', 'nav navbar-nav');

        $menu->addChild('User', ['route' => 'account-edit']);
        $menu->addChild('Security', ['route' => 'account-security']);
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

        /** @var Screen $screen */
        $screen = $options['screen'];
        $guid = $screen->getGuid();

        $menu->addChild('Common', [
            'route' => 'management-screen-data',
            'routeParameters' => ['guid' => $guid],
        ]);

        if ($this->authorizationChecker->isGranted('schedule', $screen)) {
            $menu->addChild('Schedule', [
                'route' => 'management-screen-schedule',
                'routeParameters' => ['guid' => $guid],
            ]);
        } else {
            $menu->addChild('Schedule', [
                'route' => '',
                'routeParameters' => [],
                'attributes' => ['class' => 'disabled item'],
            ]);
        }

        if ($this->authorizationChecker->isGranted('manage', $screen)) {
            $menu->addChild('Rights', [
                'route' => 'management-screen-rights',
                'routeParameters' => ['guid' => $guid],
            ]);
        } else {
            $menu->addChild('Rights', [
                'route' => '',
                'routeParameters' => [],
                'attributes' => ['class' => 'disabled item'],
            ]);
        }

        if ($this->authorizationChecker->isGranted('schedule', $screen)) {
            $menu->addChild('Offline', [
                'route' => 'management-screen-offline',
                'routeParameters' => ['guid' => $guid],
            ]);
        } else {
            $menu->addChild('Offline', [
                'route' => '',
                'routeParameters' => [],
                'attributes' => ['class' => 'disabled item'],
            ]);
        }

        if ($this->authorizationChecker->isGranted('manage', $screen)) {
            $menu->addChild('Alarming', [
                'route' => 'management-screen-alarming',
                'routeParameters' => ['guid' => $guid],
            ]);
        } else {
            $menu->addChild('Alarming', [
                'route' => '',
                'routeParameters' => [],
                'attributes' => ['class' => 'disabled item'],
            ]);
        }

        return $menu;
    }
}
