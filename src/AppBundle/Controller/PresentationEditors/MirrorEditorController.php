<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Controller\PresentationEditors;

use AppBundle\Entity\Presentation;
use AppBundle\Entity\PresentationSettings\Mirror;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MirrorEditorController extends AbstractPresentationEditor
{
    /**
     * @Route(
     *     "/manage/presentations/editor/mirror/{presentationId}",
     *     name="presentation-editor-mirror",
     *     requirements={"presentationId": ".*"}
     * )
     */
    public function editAction(Request $request, int $presentationId): Response
    {
        $presentation = $this->getPresentation($presentationId);
        if (!$this->supports($presentation)) {
            throw new \RuntimeException('Presentation type not supported.');
        }

        $serializer = $this->get('jms_serializer');
        try {
            $setttings = $serializer->deserialize($presentation->getSettings(), Mirror::class, 'json');
        } catch (\Throwable $ex) {
            $setttings = new Mirror();
        }

        $form = $this->get('form.factory')
            ->createNamedBuilder('form_presentation', FormType::class, $setttings)
            ->add('url', TextType::class)
            ->add('type', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $presentation->setSettings($serializer->serialize($setttings, 'json'));
            $presentation->setLastModified(new \DateTime('now'));
            $em->persist($presentation);
            $em->flush();

            // @TODO Flash Message that save was successfull
        }

        return $this->render('manage/presentations/editor_mirror.html.twig', [
            'presentation' => $presentation,
            'form' => $form->createView(),
        ]);
    }

    public function supports(Presentation $presentation): bool
    {
        return 'mirror' === $presentation->getType();
    }
}
