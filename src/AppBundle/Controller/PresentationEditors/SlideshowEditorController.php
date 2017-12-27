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
use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SlideshowEditorController extends AbstractPresentationEditor
{
    /**
     * @param Request $request
     * @param         $presentationId
     *
     * @return Response
     *
     * @throws \RuntimeException
     *
     * @Route(
     *     "/manage/presentations/editor/slideshow/{presentationId}",
     *     name="presentation-editor-slideshow",
     *     requirements={"presentationId": "[0-9]+"}
     * )
     */
    public function editAction(/** @scrutinizer ignore-unused */ Request $request, $presentationId)
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
        $settings = $this->getCurrentSettingsOrEmpty($presentation);
        $settings->setSlides($slides);
        $presentation->setSettings($serializer->serialize($settings, 'json'));

        $em = $this->get('doctrine.orm.entity_manager');
        $em->persist($presentation);
        $em->flush();

        return new Response('', 204);
    }

    /**
     * @Route(
     *     "/manage/presentations/editor/slideshow/{presentationId}/fileTree",
     *     name="presentation-editor-slideshow-filetree",
     *     requirements={"presentationId": "[0-9]+"}
     * )
     */
    public function fileTreeAction()
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $filePool = $this->get('app.filepool');
        $fileTreeBuilder = $this->get('app.jstree.builder');

        $fileTreeBuilder->addNewRoot($filePool->getPathForUser($user), 'me');

        $serializer = $this->get('jms_serializer');
        return new Response($serializer->serialize($fileTreeBuilder->getTree(), 'json'));
    }

    /**
     * @param Presentation $presentation
     *
     * @return bool
     */
    public function supports(Presentation $presentation): bool
    {
        return ($presentation->getType() === 'slideshow');
    }

    /**
     * @param Presentation $presentation
     *
     * @return Slideshow
     */
    protected function getCurrentSettingsOrEmpty(Presentation $presentation): Slideshow
    {
        $serializer = $this->get('jms_serializer');
        try {
            $settings = $serializer->deserialize($presentation->getSettings(), Slideshow::class, 'json');
        } catch (\Exception $exception) {
            $settings = new Slideshow();
        }
        return $settings;
    }
}
