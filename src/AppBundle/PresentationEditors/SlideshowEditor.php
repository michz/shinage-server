<?php

namespace AppBundle\PresentationEditors;

use AppBundle\Entity\Presentation;
use AppBundle\Entity\PresentationSettings\Mirror;
use AppBundle\Entity\PresentationSettings\Slideshow;
use AppBundle\Entity\Slides\ImageSlide;
use AppBundle\Entity\Slides\SlideCollection;
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
        if ($this->parameters === 'update') {
            return $this->updateAction($request);
        }

        $serializer = $this->container->get('jms_serializer');
        $settings = $serializer->deserialize($this->presentation->getSettings(), Slideshow::class, 'json');

        $slides = $settings->getSlides();
        $slidesJson = $serializer->serialize($slides, 'json');

        return new Response($this->container->get('twig')->render('manage/presentations/editor_slideshow.html.twig', [
            'presentationId' => $this->presentation->getId(),
            'slidesJson' => $slidesJson,
        ]));
    }

    protected function updateAction(Request $request)
    {
        $slidesJson = $request->get('slides');
        $serializer = $this->container->get('jms_serializer');
        $slides = $serializer->deserialize($slidesJson, 'array<'.ImageSlide::class.'>', 'json');

        /** @var Slideshow $settings */
        $settingsJson = $this->presentation->getSettings();
        $settings = $serializer->deserialize($settingsJson, Slideshow::class, 'json');
        $settings->setSlides($slides);
        $this->presentation->setSettings($serializer->serialize($settings, 'json'));

        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->persist($this->presentation);
        $em->flush();

        return new Response('done');
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

/*
$image1 = new ImageSlide();
$image2 = new ImageSlide();
$slides = new SlideCollection();
$slides->addSlide($image1)
    ->addSlide($image2);

dump($slides);

$serializer = $this->container->get('jms_serializer');
$json = $serializer->serialize($slides, 'json');

dump($json);

$jsonIn = '{"slides":[{"duration":1000,"title":"Slide","transition":"","type":"Image","src":"123"},{"duration":1000,"title":"Slide","transition":"","type":"Image","src":""}]}';
$arr = $serializer->deserialize($jsonIn, SlideCollection::class, 'json');
dump($arr);
*/
