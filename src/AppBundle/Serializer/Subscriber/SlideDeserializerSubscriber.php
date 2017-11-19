<?php

namespace AppBundle\Serializer\Subscriber;

use AppBundle\Entity\Slides\Slide;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;

/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  08.11.17
 * @time     :  20:26
 */
class SlideDeserializerSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [[
            'event'     => 'serializer.pre_deserialize',
            'method'    => 'onPreDeserialize',
            'class'     => Slide::class,
            'format'    => 'json',
            'priority'  => 0,
        ]];
    }

    public function onPreDeserialize(PreDeserializeEvent $event)
    {
        $data = $event->getData();
        $type = $data['type'];
        $slideClass = '\\AppBundle\\Entity\\Slides\\'.$type.'Slide';

        if (!class_exists($slideClass)) {
            throw new \Exception('Slide type not found: '.$type);
        }

        $event->setType($slideClass);
    }
}
