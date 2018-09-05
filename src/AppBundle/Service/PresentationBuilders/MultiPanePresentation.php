<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Service\PresentationBuilders;

use AppBundle\Entity\Presentation;
use AppBundle\Entity\ScreenRemote\PlayablePresentation;
use JMS\Serializer\SerializerInterface;

/**
 * @deprecated
 */
class MultiPanePresentation implements PresentationBuilderInterface
{
    const PRESENTATION_TYPE = 'multipane';

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
        // @TODO
        return new PlayablePresentation();
    }

    public function getLastModified(Presentation $presentation): \DateTime
    {
        return $presentation->getLastModified();
    }
}
