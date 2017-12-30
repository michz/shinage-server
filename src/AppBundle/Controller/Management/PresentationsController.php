<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 05.01.17
 * Time: 09:21
 */

namespace AppBundle\Controller\Management;

use AppBundle\Entity\Presentation;
use AppBundle\Service\PresentationBuilders\PresentationBuilderChain;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;

class PresentationsController extends Controller
{

    /**
     * @Route("/manage/presentations", name="management-presentations")
     */
    public function managePresentationsAction()
    {
        // @TODO Security
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $pres = $this->getPresentationsForUser($user);

        return $this->render('manage/presentations/pres-main.html.twig', [
            'presentations' => $pres,
        ]);
    }

    /**
     * @Route("/manage/presentations/create", name="management-presentations-create")
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function createPresentationAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        /** @var PresentationBuilderChain $builderChain */
        $builderChain = $this->get('app.presentation_builder_chain');

        $presentation = new Presentation();
        $presentation->setType('slideshow');
        $form = $this->get('form.factory')->createNamedBuilder('form_presentation', FormType::class, $presentation)
            ->add('title', TextType::class)
            ->add('type', ChoiceType::class, [
                'choices' => $this->getTypeChoices($builderChain->getTypes()),
                'translation_domain' => 'PresentationTypes',
            ])
            ->add('save', SubmitType::class, ['label' => 'Create'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // @TODO make owner chosable
            $presentation->setOwner($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($presentation);
            $em->flush();
            return $this->redirectToRoute('management-presentations');
        }

        return $this->render('manage/presentations/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function getPresentationsForUser(User $user)
    {
        $em = $this->getDoctrine()->getManager();
        return $user->getPresentations($em);
    }

    protected function getTypeChoices($types)
    {
        $ret = [];
        foreach ($types as $type) {
            $ret[$type] = $type;
        }
        return $ret;
    }
}
