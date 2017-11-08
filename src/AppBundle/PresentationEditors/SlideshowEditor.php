<?php

namespace AppBundle\PresentationEditors;

use AppBundle\Entity\Presentation;
use AppBundle\Entity\PresentationSettings\Mirror;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  08.11.17
 * @time     :  19:18
 */
class SlideshowEditor implements PresentationEditorInterface
{
    /** @var Container */
    private $container;

    /** @var Presentation */
    private $presentation;

    private $parameters;

    public function render(Request $request)
    {

        // @TODO

        return new Response($this->container->get('twig')->render('manage/presentations/editor_slideshow.html.twig', [
            #'form' => $form->createView()
        ]));
    }

    public function setPresentation(Presentation $presentation)
    {
        $this->presentation = $presentation;
        return $this;
    }

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }

    public function setContainer($container)
    {
        $this->container = $container;
        return $this;
    }
}
