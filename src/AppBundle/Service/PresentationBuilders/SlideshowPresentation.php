<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Service\PresentationBuilders;

use AppBundle\Entity\Presentation;
use AppBundle\Entity\PresentationSettings\Slideshow;
use AppBundle\Entity\ScreenRemote\PlayablePresentation;
use AppBundle\Entity\ScreenRemote\PlayablePresentationSettings;
use JMS\Serializer\SerializerInterface;

class SlideshowPresentation implements PresentationBuilderInterface
{
    const PRESENTATION_TYPE = 'slideshow';

    /** @var SerializerInterface */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function supports(Presentation $presentation): bool
    {
        return self::PRESENTATION_TYPE === $presentation->getType();
    }

    /**
     * @return string[]|array
     */
    public function getSupportedTypes(): array
    {
        return [self::PRESENTATION_TYPE];
    }

    public function buildPresentation(Presentation $presentation): PlayablePresentation
    {
        $playable = new PlayablePresentation();
        $playable->settings = new PlayablePresentationSettings();
        $playable->settings->backgroundColor = '#000';
        /** @var Slideshow $settings */
        $settings = $this->serializer->deserialize($presentation->getSettings(), Slideshow::class, 'json');
        $playable->slides = $settings->getSlides();
        return $playable;
    }

    public function getLastModified(Presentation $presentation): \DateTime
    {
        return $presentation->getLastModified();
    }
}
