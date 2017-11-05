<?php

namespace AppBundle\Service\PresentationBuilders;

use AppBundle\Entity\Presentation;
use AppBundle\PresentationEditors\PresentationEditorInterface;
use Symfony\Component\DependencyInjection\Container;

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
     * @param Presentation $presentation
     * @param string       $parameters
     * @param Container    $container
     *
     * @return PresentationEditorInterface
     */
    public function getEditor(Presentation $presentation, $parameters, Container $container);

    /**
     * @return array
     */
    public function getSupportedTypes();
}
