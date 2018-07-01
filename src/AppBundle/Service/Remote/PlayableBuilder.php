<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Service\Remote;

use AppBundle\Entity\Presentation;
use AppBundle\Entity\ScreenRemote\PlayablePresentation;
use Symfony\Component\Routing\RouterInterface;

class PlayableBuilder
{
    /** @var RouterInterface */
    protected $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function build(
        Presentation $presentation,
        /* @scrutinizer ignore-unused */ string $hostScheme
    ): PlayablePresentation {
        $playable = new PlayablePresentation();
        $playable->lastModified = $presentation->getLastModified();

        return $playable;
    }

    public function getPlayerSlideType(string $internalType): string
    {
        return ucwords($internalType);
    }
}
