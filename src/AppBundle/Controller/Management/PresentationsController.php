<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 05.01.17
 * Time: 09:21
 */

namespace AppBundle\Controller\Management;

use AppBundle\AppBundle;
use AppBundle\Entity\Presentation;
use AppBundle\Service\PresentationBuilders\PresentationBuilderChain;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Exceptions\NoScreenGivenException;
use AppBundle\Entity\Screen;
use AppBundle\Service\FilePool;
use AppBundle\Service\Pool\PoolDirectory;
use AppBundle\Service\Pool\PoolItem;
use AppBundle\Entity\User;
use AppBundle\Entity\Slide;
use AppBundle\Exceptions\SlideTypeNotImplemented;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PresentationsController extends Controller
{

    /**
     * @Route("/manage/presentations", name="management-presentations")
     */
    public function managePresentationsAction(Request $request)
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
     */
    public function createPresentationAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        /** @var PresentationBuilderChain $builderChain */
        $builderChain = $this->get('app.presentation_builder_chain');

        $presentation = new Presentation();
        $form = $this->get('form.factory')->createNamedBuilder('form_presentation', FormType::class, $presentation)
            ->add('title', TextType::class)
            ->add('type', ChoiceType::class, ['choices' => $this->getTypeChoices($builderChain->getTypes())])
            #->add('password', PasswordType::class)
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

    /**
     * @Route(
     *     "/manage/presentations/edit/{id}/{parameters}",
     *     name="management-presentations-edit",
     *     requirements={"id": "[0-9]+", "parameters": ".*"},
     *     defaults={"parameters": "/"}
     *     )
     */
    public function editPresentationAction(Request $request, $id, $parameters)
    {
        return $this->render('manage/presentations/edit.html.twig', [
            'editControllerAction' => 'AppBundle:Management\\Presentations:editPresentationEditor',
            'presentation' => $id,
            'parameters' => $parameters,
        ]);
    }

    /**
     * No public route, no direct access.
     *
     * @param Request $request
     * @param int     $id
     * @param string  $parameters
     *
     * @return Response
     */
    public function editPresentationEditorAction(Request $request, $id, $parameters)
    {
        // get parent controller's request
        $masterRequest = $this->get('request_stack')->getMasterRequest();

        $presentation = $this->getDoctrine()->getEntityManager()->find('AppBundle:Presentation', $id);

        $builderChain = $this->get('app.presentation_builder_chain');
        $builder = $builderChain->getBuilderForPresentation($presentation);
        $editor = $builder->getEditor($presentation, $parameters, $this->container);
        return $editor->render($masterRequest);
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
