<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Service\PresentationBuilders;

use AppBundle\Entity\Presentation;
use AppBundle\Entity\ScreenRemote\PlayablePresentation;

/**
 * @deprecated
 */
interface PresentationBuilderInterface
{
    public function supports(Presentation $presentation): bool;

    /**
     * @return PlayablePresentation|string
     */
    public function buildPresentation(Presentation $presentation);

    public function getLastModified(Presentation $presentation): \DateTime;

    /**
     * @return string[]|array
     */
    public function getSupportedTypes(): array;
}
