<?php
/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  19.11.17
 * @time     :  10:52
 */

namespace AppBundle\Controller\PresentationEditors;

use AppBundle\Entity\Presentation;
use AppBundle\Entity\PresentationSettings\Slideshow;
use AppBundle\Entity\Slides\ImageSlide;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
     *
     * @throws \RuntimeException
     */
    public function editAction(Request $request, $presentationId)
    {
        $presentation = $this->getPresentation($presentationId);
        if (!$this->supports($presentation)) {
            throw new \RuntimeException('Presentation type not supported.');
        }

        $serializer = $this->get('jms_serializer');
        $settings = $serializer->deserialize($presentation->getSettings(), Slideshow::class, 'json');

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
     *
     * @throws \RuntimeException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function updateAction(Request $request, $presentationId)
    {
        $presentation = $this->getPresentation($presentationId);
        if (!$this->supports($presentation)) {
            throw new \RuntimeException('Presentation type not supported.');
        }

        $slidesJson = $request->get('slides');
        $serializer = $this->get('jms_serializer');
        $slides = $serializer->deserialize($slidesJson, 'array<'.ImageSlide::class.'>', 'json');

        /** @var Slideshow $settings */
        $settingsJson = $presentation->getSettings();
        $settings = $serializer->deserialize($settingsJson, Slideshow::class, 'json');
        $settings->setSlides($slides);
        $presentation->setSettings($serializer->serialize($settings, 'json'));

        $em = $this->get('doctrine.orm.entity_manager');
        $em->persist($presentation);
        $em->flush();

        return new Response('', 204);
    }

    public function supports(Presentation $presentation): bool
    {
        return ($presentation->getType() === 'slideshow');
    }
}
