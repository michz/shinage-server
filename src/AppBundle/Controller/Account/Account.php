<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 07.01.17
 * Time: 20:31
 */

namespace AppBundle\Controller\Account;

use AppBundle\Entity\Organization;
use AppBundle\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class Account extends Controller
{
    /**
     * @Route("/account", name="account")
     */
    public function indexAction(Request $request)
    {
        return $this->redirectToRoute('account-edit');
    }

    /**
     * @Route("/account/edit", name="account-edit")
     */
    public function editAction(Request $request)
    {
        /** @var User $user_logged_in */
        $user = $this->get('security.token_storage')->getToken()->getUser();
        //$user = $user_logged_in->
        $userManager = $this->container->get('fos_user.user_manager');
        $i18n = $this->get('translator');

        $form = $this->get('form.factory')->createNamedBuilder('form1name', FormType::class, $user)
            ->add('email', EmailType::class)
            #->add('password', PasswordType::class)
            ->add('save', SubmitType::class, array('label' => 'Save'))
            ->getForm();


        $form_pw = $this->get('form.factory')->createNamedBuilder('form2name', FormType::class, $user)
            ->add('old-password', PasswordType::class, array('label'=>'oldPassword', 'mapped' => false))
            ->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'PasswordsMustMatch',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => true,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'PasswordAgain'),
            ))
            ->add('save', SubmitType::class, array('label' => 'Save'))
            ->getForm();



        if ('POST' === $request->getMethod()) {
            if ($request->request->has('form1name')) {
                $form->handleRequest($request);

                if ($form->isSubmitted()) {
                    if ($form->isValid()) {
                        $userManager->updateUser($user, true);
                        $this->addFlash('success', 'Die Änderungen wurden gespeichert.');
                    } else {
                        $this->addFlash(
                            'error',
                            'Die Änderungen konnten leider nicht gespeichert werden. '.
                            'Evtl. sind Eingaben nicht korrekt.'
                        );
                    }
                }
            }

            if ($request->request->has('form2name')) {
                $form_pw->handleRequest($request);

                if ($form_pw->isSubmitted()) {
                    $encoderFactory = $this->get('security.encoder_factory');
                    $encoder = $encoderFactory->getEncoder($user);

                    if (!$encoder->isPasswordValid(
                        $user->getPassword(),
                        $form_pw->get('old-password')->getData(),
                        $user->getSalt()
                    )) {
                        // wrong old password
                        $form_pw->get('old-password')->addError(
                            new FormError($i18n->trans('WrongOldPassword'))
                        );
                    } else {
                        if ($form_pw->isValid()) {
                            // now check if it is empty
                            $pw = $form_pw->get('plainPassword')->getData();
                            if (empty($pw)) {
                                $this->addFlash('error', 'Das Passwort darf nicht leer sein.');
                            } else {
                                // everythin seems ok, now set password and save
                                $user->setPlainPassword($pw);

                                $userManager->updateUser($user, true);
                                $this->addFlash('success', 'Das Passwort wurde erfolgreich geändert.');
                            }
                        }
                    }
                }
            }
        }



        return $this->render('account/user.html.twig', [
            'form' => $form->createView(),
            'form_pw' => $form_pw->createView()
        ]);
    }


    /**
     * @Route("/account/organizations", name="account-organizations")
     */
    public function orgaAction(Request $request)
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $orga_new = new Organization();

        $form_create = $this->createFormBuilder($orga_new)
            ->add('Name', TextType::class, array('trim' => true))
            ->add('save', SubmitType::class, array('label' => 'Save'))
            ->getForm();
        $form_create->handleRequest($request);
        if ($form_create->isSubmitted()) {
            if ($form_create->isValid()) {
                $em->persist($orga_new);

                try {
                    $em->flush();
                    $user->addOrganization($orga_new);
                    $em->persist($user);
                    $em->flush();
                    $em->refresh($orga_new); // needed to notify $user that he is in a new organization
                    $this->addFlash('success', 'Die neue Organisation wurde gespeichert.');
                } catch (UniqueConstraintViolationException $ex) {
                    $this->addFlash(
                        'error',
                        'Der gewählte Name wird bereits für eine Organisation verwendet. '.
                        'Bitte wähle einen anderen, eindeutigen Namen.'
                    );
                    $em = $this->getDoctrine()->resetManager();
                }
            } else {
                $this->addFlash('error', 'Die Organisation konnte leider nicht angelegt werden.');
            }
        }


        $orgas = $user->getOrganizations();
        return $this->render('account/organizations.html.twig', [
            'form_create' => $form_create->createView(),
            'organizations' => $orgas
        ]);
    }

    /**
     * @Route("/account/organizations/leave/{id}", name="account-orga-leave")
     */
    public function orgaLeaveAction(Request $request, $id)
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $organization = $em->find('AppBundle\Entity\Organization', $id);
        $user->removeOrganization($organization);
        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'Die Organisation wurde verlassen.');
        return $this->redirectToRoute('account-organizations');
    }

    /**
     * @Route("/account/organizations/add-user", name="account-orga-add-user")
     */
    public function orgaAddUserAction(Request $request)
    {
        /** @var User $user */
        /** @var User $user_new */
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $rep = $em->getRepository('AppBundle:User');
        $user_new = $rep->findOneBy(array('email' => $request->get('email')));
        $orga = $em->find('AppBundle\Entity\Organization', $request->get('organization'));

        // check if user is allowed to edit organization
        if (!$user->getOrganizations()->contains($orga)) {
            $this->addFlash('error', 'Sie dürfen diese Organisation leider nicht bearbeiten.');
            return $this->redirectToRoute('account-organizations');
        }

        if (!$user_new) {
            $this->addFlash('error', 'Es wurde kein Benutzer mit der angegebenen E-Mail-Adresse gefunden.');
            return $this->redirectToRoute('account-organizations');
        }

        if ($user_new->getOrganizations()->contains($orga)) {
            $this->addFlash('notice', 'Der Benutzer (' . $user_new->getEmail() .
                ') ist bereits Mitglied der Organisation (' . $orga->getName() . ').');
            return $this->redirectToRoute('account-organizations');
        }

        $user_new->addOrganization($orga);

        $em->persist($user_new);
        $em->flush();

        $this->addFlash(
            'success',
            'Der Benutzer (' . $user_new->getEmail() .
            ') wurde der Organisation (' . $orga->getName() . ') hinzugefügt.'
        );
        return $this->redirectToRoute('account-organizations');
    }


    /**
     * @Route("/account/organizations/remove/{orga_id}/{user_id}", name="account-orga-remove")
     */
    public function orgaRemoveAction(Request $request, $orga_id, $user_id)
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $organization = $em->find('AppBundle\Entity\Organization', $orga_id);
        $user_other = $em->find('AppBundle\Entity\User', $user_id);

        // check if user is allowed to edit organization
        if (!$user->getOrganizations()->contains($organization)) {
            $this->addFlash('error', 'Sie dürfen diese Organisation leider nicht bearbeiten.');
            return $this->redirectToRoute('account-organizations');
        }

        $user_other->removeOrganization($organization);
        $em->persist($user_other);
        $em->flush();

        $this->addFlash('success', 'Die Benutzer (' . $user_other->getEmail() .
            ') wurde aus der Organisation (' . $organization->getName() . ') entfernt.');
        return $this->redirectToRoute('account-organizations');
    }
}
