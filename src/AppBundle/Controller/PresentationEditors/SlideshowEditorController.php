<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Controller\PresentationEditors;

use AppBundle\Entity\Presentation;
use AppBundle\Presentation\Slideshow\Settings;
use AppBundle\Presentation\Slideshow\Slides\ImageSlide;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SlideshowEditorController extends AbstractPresentationEditor
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var SerializerInterface */
    private $serializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    public function editAction(int $presentationId): Response
    {
        $presentation = $this->getPresentation($presentationId);
        if (!$this->supports($presentation)) {
            throw new \RuntimeException('Presentation type not supported.');
        }

        $settings = $this->getCurrentSettingsOrEmpty($presentation);

        $slides = $settings->getSlides();
        $slidesJson = $this->serializer->serialize($slides, 'json');

        return $this->render('manage/presentations/editor_slideshow.html.twig', [
            'presentation' => $presentation,
            'slidesJson' => $slidesJson,
        ]);
    }

    public function updateAction(Request $request, int $presentationId): Response
    {
        $presentation = $this->getPresentation($presentationId);
        if (!$this->supports($presentation)) {
            throw new \RuntimeException('Presentation type not supported.');
        }

        $slidesJson = $request->get('slides');
        $slides = $this->serializer->deserialize($slidesJson, 'array<' . ImageSlide::class . '>', 'json');

        /** @var Settings $settings */
        $settings = $this->getCurrentSettingsOrEmpty($presentation);
        $settings->setSlides($slides);
        $presentation->setSettings($this->serializer->serialize($settings, 'json'));

        $this->entityManager->persist($presentation);
        $this->entityManager->flush();

        return new Response('', 204);
    }

    public function supports(Presentation $presentation): bool
    {
        return 'slideshow' === $presentation->getType();
    }

    protected function getCurrentSettingsOrEmpty(Presentation $presentation): Settings
    {
        try {
            $settings = $this->serializer->deserialize($presentation->getSettings(), Settings::class, 'json');
        } catch (\Throwable $exception) {
            $settings = new Settings();
        }
        return $settings;
    }
}
