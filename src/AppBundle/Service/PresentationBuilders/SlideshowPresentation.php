<?php

namespace AppBundle\Service\PresentationBuilders;

use AppBundle\Entity\Presentation;
use AppBundle\Entity\PresentationSettings\Slideshow;
use AppBundle\Entity\ScreenRemote\PlayablePresentation;
use AppBundle\Entity\ScreenRemote\PlayablePresentationSettings;
use JMS\Serializer\SerializerInterface;

/**
 * @author   :  Michael Zapf <m.zapf@mtx.de>
 * @date     :  06.11.17
 * @time     :  20:58
 */
class SlideshowPresentation implements PresentationBuilderInterface
{
    const PRESENTATION_TYPE = 'slideshow';

    /** @var SerializerInterface */
    private $serializer;

    /**
     * SlideshowPresentation constructor.
     *
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function supports(Presentation $presentation)
    {
        return ($presentation->getType() === self::PRESENTATION_TYPE);
    }

    public function getSupportedTypes()
    {
        return [self::PRESENTATION_TYPE];
    }

    public function buildPresentation(Presentation $presentation)
    {
        $playable = new PlayablePresentation();
        $playable->settings = new PlayablePresentationSettings();
        $playable->settings->backgroundColor = '#000';
        /** @var Slideshow $settings */
        $settings = $this->serializer->deserialize($presentation->getSettings(), Slideshow::class, 'json');
        $playable->slides = $settings->getSlides();
        return $playable;
    }

    public function getLastModified(Presentation $presentation)
    {
        return $presentation->getLastModified();
    }
}
