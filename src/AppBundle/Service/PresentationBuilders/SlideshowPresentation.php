<?php

namespace AppBundle\Service\PresentationBuilders;

use AppBundle\Entity\Presentation;
use AppBundle\PresentationEditors\MirrorEditor;
use AppBundle\PresentationEditors\SlideshowEditor;
use Symfony\Component\DependencyInjection\Container;

/**
 * @author   :  Michael Zapf <m.zapf@mtx.de>
 * @date     :  06.11.17
 * @time     :  20:58
 */
class SlideshowPresentation implements PresentationBuilderInterface
{
    const PRESENTATION_TYPE = 'slideshow';

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
        // @TODO build/return json
    }

    public function getLastModified(Presentation $presentation)
    {
        return $presentation->getLastModified();
    }

    public function getEditor(Presentation $presentation, $parameters, Container $container)
    {
        $editor = new SlideshowEditor();
        $editor->setPresentation($presentation)
            ->setParameters($parameters)
            ->setContainer($container);
        return $editor;
    }
}
