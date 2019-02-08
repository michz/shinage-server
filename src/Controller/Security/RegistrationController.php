<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Security;

use App\Entity\User;
use FOS\UserBundle\Doctrine\UserManager;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class RegistrationController extends Controller
{
    public function indexAction(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        //$rep = $this->getDoctrine()->getRepository('App:Screen');
        $user = new User();
        /** @var UserManager $userManager */
        $userManager = $this->get('fos_user.user_manager');

        $form = $this->createFormBuilder($user)
            ->add('email', EmailType::class)
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'PasswordsMustMatch',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'PasswordAgain'],
            ])
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // check if there is already a user with same email
                $oldUser = $userManager->findUserByEmail($user->getEmail());
                if (null !== $oldUser) {
                    $this->addFlash(
                        'error',
                        'Die genannte E-Mail-Adresse wird leider bereits genutzt.'
                    );
                } else {
                    // good news: email is not used yet, register user
                    $user->setPlainPassword($form->get('password')->getData());
                    $user->setConfirmationToken(User::generateToken());
                    $userManager->updateUser($user, true);

                    $this->addFlash(
                        'success',
                        'Willkommen! Bitte bestätige nun deine E-Mail-Adresse.'
                    );

                    $this->sendRegistrationMail($user);
                }
            } else {
                $this->addFlash(
                    'error',
                    'Die Registrierung war leider nicht möglich. Vielleicht war eine der Eingaben nicht korrekt?'
                );
            }
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function confirmAction(string $confirmationToken): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        /** @var UserManager $userManager */
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserByConfirmationToken($confirmationToken);

        if (null === $user) {
            $this->addFlash(
                'error',
                'Zu dem Bestätigungscode konnte leider kein Benutzer gefunden werden.'
            );
            return $this->redirectToRoute('fos_user_security_login');
        }

        $user->setEnabled(true);
        $userManager->updateUser($user);
        $this->addFlash(
            'success',
            'Vielen Dank! Dein Konto ist nun freigeschaltet und du kannst dich einloggen.'
        );
        return $this->redirectToRoute('fos_user_security_login');
    }

    public function sendRegistrationMail(UserInterface $user): void
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($this->get('translator')->trans('SubjectRegistrationMail'))
            ->setFrom($this->getParameter('mailer_from'))
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    'mail/de/registration.html.twig',
                    ['user' => $user]
                ),
                'text/html'
            )
            ->addPart(
                $this->renderView(
                    'mail/de/registration.txt.twig',
                    ['user' => $user]
                ),
                'text/plain'
            );
        $this->get('mailer')->send($message);
    }
}
