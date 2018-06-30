<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Controller\PresentationEditors;

use AppBundle\Entity\Presentation;
use AppBundle\Entity\PresentationSettings\Slideshow;
use AppBundle\Entity\Slides\ImageSlide;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SlideshowEditorController extends AbstractPresentationEditor
{
    /**
     * @Route(
     *     "/manage/presentations/editor/slideshow/{presentationId}",
     *     name="presentation-editor-slideshow",
     *     requirements={"presentationId": "[0-9]+"}
     * )
     */
    public function editAction(int $presentationId): Response
    {
        $presentation = $this->getPresentation($presentationId);
        if (!$this->supports($presentation)) {
            throw new \RuntimeException('Presentation type not supported.');
        }

        $serializer = $this->get('jms_serializer');
        $settings = $this->getCurrentSettingsOrEmpty($presentation);

        $slides = $settings->getSlides();
        $slidesJson = $serializer->serialize($slides, 'json');

        return $this->render('manage/presentations/editor_slideshow.html.twig', [
            'presentation' => $presentation,
            'slidesJson' => $slidesJson,
        ]);
    }

    /**
     * @Route(
     *     "/manage/presentations/editor/slideshow/{presentationId}/update",
     *     name="presentation-editor-slideshow-update",
     *     requirements={"presentationId": "[0-9]+"}
     * )
     */
    public function updateAction(Request $request, int $presentationId): Response
    {
        $presentation = $this->getPresentation($presentationId);
        if (!$this->supports($presentation)) {
            throw new \RuntimeException('Presentation type not supported.');
        }

        $slidesJson = $request->get('slides');
        $serializer = $this->get('jms_serializer');
        $slides = $serializer->deserialize($slidesJson, 'array<' . ImageSlide::class . '>', 'json');

        /** @var Slideshow $settings */
        $settings = $this->getCurrentSettingsOrEmpty($presentation);
        $settings->setSlides($slides);
        $presentation->setSettings($serializer->serialize($settings, 'json'));

        $em = $this->get('doctrine.orm.entity_manager');
        $em->persist($presentation);
        $em->flush();

        return new Response('', 204);
    }

    public function supports(Presentation $presentation): bool
    {
        return 'slideshow' === $presentation->getType();
    }

    protected function getCurrentSettingsOrEmpty(Presentation $presentation): Slideshow
    {
        $serializer = $this->get('jms_serializer');
        try {
            $settings = $serializer->deserialize($presentation->getSettings(), Slideshow::class, 'json');
        } catch (\Throwable $exception) {
            $settings = new Slideshow();
        }
        return $settings;
    }
}
