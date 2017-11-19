<?php
/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  19.11.17
 * @time     :  10:52
 */

namespace AppBundle\Controller\PresentationEditors;

use AppBundle\Entity\Presentation;
use AppBundle\Entity\PresentationSettings\Mirror;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MirrorEditorController extends AbstractPresentationEditor
{
    /**
     * @Route(
     *     "/manage/presentations/editor/mirror/{presentationId}",
     *     name="presentation-editor-mirror",
     *     requirements={"presentationId": ".*"}
     * )
     *
     * @throws \RuntimeException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function editAction(Request $request, $presentationId)
    {
        $presentation = $this->getPresentation($presentationId);
        if (!$this->supports($presentation)) {
            throw new \RuntimeException('Presentation type not supported.');
        }


        $serializer = $this->get('jms_serializer');
        try {
            $setttings = $serializer->deserialize($presentation->getSettings(), Mirror::class, 'json');
        } catch (\Exception $ex) {
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
        return ($presentation->getType() === 'mirror');
    }
}
