<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 07.01.17
 * Time: 20:31
 */

namespace AppBundle\Controller\Security;


use AppBundle\Entity\Organization;
use AppBundle\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use FOS\UserBundle\Doctrine\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;



class Registration extends Controller
{
    /**
     * @Route("/registration", name="registration")
     */
    public function indexAction(Request $request)
    {
        //$rep = $this->getDoctrine()->getRepository('AppBundle:Screen');
        $user = new User();
        /** @var UserManager $userManager */
        $userManager = $this->container->get('fos_user.user_manager');

        $form = $this->createFormBuilder($user)
            ->add('email', EmailType::class)
            ->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'PasswordsMustMatch',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => true,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'PasswordAgain'),
            ))
            ->add('save', SubmitType::class, array('label' => 'Save'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // check if there is already a user with same email
                $oldUser = $userManager->findUserByEmail($user->getEmail());
                if ($oldUser != null) {
                    $this->addFlash(
                        'error',
                        'Die genannte E-Mail-Adresse wird leider bereits genutzt.'
                    );
                } else {
                    // good news: email is not used yet, register user
                    $user->setPlainPassword($form->get('password')->getData());
                    $user->setConfirmationToken(User::generateToken());
                    $userManager->updateUser($user, true);

                    // TODO{s:5} Confirmation-E-Mail senden
                    $this->addFlash(
                        'success',
                        'Willkommen! Bitte bestätige nun deine E-Mail-Adresse.'
                    );
                }
            }
            else {
                $this->addFlash(
                    'error',
                    'Die Registrierung war leider nicht möglich. Vielleicht war eine der Eingaben nicht korrekt?'
                );
            }
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/registration/confirm/{confirmationToken}", name="registration-confirm")
     */
    public function confirmAction(Request $request, $confirmationToken)
    {
        /** @var UserManager $userManager */
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->findUserByConfirmationToken($confirmationToken);

        if ($user == null) {
            $this->addFlash(
                'error',
                'Zu dem Bestätigungscode konnte leider kein Benutzer gefunden werden.'
            );
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $user->setEnabled(true);
            $userManager->updateUser($user);
            $this->addFlash(
                'success',
                'Vielen Dank! Dein Konto ist nun freigeschaltet und du kannst dich einloggen.'
            );
            return $this->redirectToRoute('fos_user_security_login');
        }

    }

}
