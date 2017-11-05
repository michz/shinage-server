<?php

namespace AppBundle\PresentationEditors;

use AppBundle\Entity\Presentation;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  03.11.17
 * @time     :  19:56
 */
interface PresentationEditorInterface
{

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function render(Request $request);

    /**
     * @param Presentation $presentation
     *
     * @return PresentationEditorInterface
     */
    public function setPresentation(Presentation $presentation);

    /**
     * @param string $parameters
     *
     * @return PresentationEditorInterface
     */
    public function setParameters($parameters);

    /**
     * @param Container $container
     *
     * @return PresentationEditorInterface
     */
    public function setContainer($container);
}
