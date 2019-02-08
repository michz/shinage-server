<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\PresentationEditors;

use App\Entity\PresentationInterface;
use App\Presentation\RevealJs\Settings;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RevealJsEditorController extends AbstractPresentationEditor
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

    public function editAction(Request $request, int $presentationId): Response
    {
        $presentation = $this->getPresentation($presentationId);
        if (!$this->supports($presentation)) {
            throw new \RuntimeException('Presentation type not supported.');
        }

        try {
            $settings = $this->serializer->deserialize($presentation->getSettings(), Settings::class, 'json');
        } catch (\Throwable $ex) {
            $settings = new Settings();
        }

        $form = $this->get('form.factory')
            ->createNamedBuilder(
                'form_presentation',
                FormType::class,
                $settings,
                ['translation_domain' => 'RevealJsPresentationEditor']
            )
            ->add(
                'content',
                TextareaType::class,
                [
                    'attr' => ['data-codemirror' => 'true', 'data-codemirror-mode' => 'htmlmixed'],
                ]
            )
            ->add(
                'revealSettings',
                TextareaType::class,
                [
                    'label'      => 'Reveal.js settings (leave empty for defaults)',
                    'required'   => false,
                    'empty_data' => '',
                    'attr' => ['data-codemirror' => 'true', 'data-codemirror-mode' => 'json'],
                ]
            )
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $presentation->setSettings($this->serializer->serialize($settings, 'json'));
            $presentation->setLastModified(new \DateTime('now'));
            $this->entityManager->persist($presentation);
            $this->entityManager->flush();

            $this->addFlash('success', 'Saved successfully');
        }

        return $this->render('manage/presentations/editor_reveal_js.html.twig', [
            'presentation' => $presentation,
            'form' => $form->createView(),
        ]);
    }

    public function supports(PresentationInterface $presentation): bool
    {
        return 'reveal_js' === $presentation->getType();
    }
}
