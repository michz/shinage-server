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
 * @date     :  03.11.17
 * @time     :  19:56
 */
class MirrorEditor implements PresentationEditorInterface
{
    /** @var Container */
    private $container;

    /** @var Presentation */
    private $presentation;

    private $parameters;

    public function render(Request $request)
    {
        $serializer = $this->container->get('serializer');
        try {
            $setttings = $serializer->deserialize($this->presentation->getSettings(), Mirror::class, 'json');
        } catch (\Exception $ex) {
            $setttings = new Mirror();
        }

        $form = $this->container->get('form.factory')
            ->createNamedBuilder('form_presentation', FormType::class, $setttings)
            ->add('url', TextType::class)
            ->add('type', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $this->presentation->setSettings($serializer->serialize($setttings, 'json'));
            $this->presentation->setLastModified(new \DateTime('now'));
            $em->persist($this->presentation);
            $em->flush();
        }

        return new Response($this->container->get('twig')->render('manage/presentations/editor_mirror.html.twig', [
            'form' => $form->createView()
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
