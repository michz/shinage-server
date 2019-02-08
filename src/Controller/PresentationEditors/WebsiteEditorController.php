<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\PresentationEditors;

use App\Entity\PresentationInterface;
use App\Presentation\Website\Settings;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebsiteEditorController extends AbstractPresentationEditor
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
            $setttings = $this->serializer->deserialize($presentation->getSettings(), Settings::class, 'json');
        } catch (\Throwable $ex) {
            $setttings = new Settings();
        }

        $form = $this->get('form.factory')
            ->createNamedBuilder('form_presentation', FormType::class, $setttings)
            ->add('url', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $presentation->setSettings($this->serializer->serialize($setttings, 'json'));
            $presentation->setLastModified(new \DateTime('now'));
            $this->entityManager->persist($presentation);
            $this->entityManager->flush();

            $this->addFlash('success', 'Saved successfully');
        }

        return $this->render('manage/presentations/editor_website.html.twig', [
            'presentation' => $presentation,
            'form' => $form->createView(),
        ]);
    }

    public function supports(PresentationInterface $presentation): bool
    {
        return 'website' === $presentation->getType();
    }
}
