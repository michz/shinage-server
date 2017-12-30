<?php

namespace AppBundle\Service\PresentationBuilders;

use AppBundle\Entity\Presentation;
use AppBundle\Entity\ScreenRemote\PlayablePresentation;

/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  27.10.17
 * @time     :  11:49
 */
interface PresentationBuilderInterface
{

    /**
     * @param Presentation $presentation
     *
     * @return bool
     */
    public function supports(Presentation $presentation);

    /**
     * @param Presentation $presentation
     *
     * @return PlayablePresentation|string
     */
    public function buildPresentation(Presentation $presentation);

    /**
     * @param Presentation $presentation
     *
     * @return \DateTime
     */
    public function getLastModified(Presentation $presentation);

    /**
     * @return array
     */
    public function getSupportedTypes();
}
