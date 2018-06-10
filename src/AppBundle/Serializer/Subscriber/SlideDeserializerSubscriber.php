<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Serializer\Subscriber;

use AppBundle\Entity\Slides\Slide;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;

class SlideDeserializerSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [[
            'event'     => 'serializer.pre_deserialize',
            'method'    => 'onPreDeserialize',
            'class'     => Slide::class,
            'format'    => 'json',
            'priority'  => 0,
        ]];
    }

    public function onPreDeserialize(PreDeserializeEvent $event): void
    {
        $data = $event->getData();
        $type = $data['type'];
        $slideClass = '\\AppBundle\\Entity\\Slides\\' . $type . 'Slide';

        if (!class_exists($slideClass)) {
            throw new \RuntimeException('Slide type not found: ' . $type);
        }

        $event->setType($slideClass);
    }
}
