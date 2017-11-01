<?php

namespace AppBundle\Service\PresentationBuilders;

use AppBundle\Entity\Presentation;

/**
 * Created by solutionDrive GmbH.
 *
 * @author   :  Michael Zapf <mz@solutionDrive.de>
 * @date     :  27.10.17
 * @time     :  11:49
 * @copyright:  2017 solutionDrive GmbH
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
     * @return \JsonSerializable|string
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
