<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Security;

use App\Entity\User;
use App\Service\HmacCalculatorInterface;
use App\Service\MailSenderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    private const MAX_RESET_LINK_AGE = 86400; // 1 day in seconds

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailSenderInterface $mailSender,
        private readonly HmacCalculatorInterface $hmacCalculator,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function loginAction(
        AuthenticationUtils $authenticationUtils,
        TokenStorageInterface $tokenStorage,
    ): Response {
        $currentToken = $tokenStorage->getToken();
        $currentUser = $currentToken?->getUser();

        if ($currentUser instanceof User) {
            return $this->redirectToRoute('management-dashboard');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    public function logoutAction(): void
    {
    }

    public function requestResetPasswordAction(
        Request $request,
    ): Response {
        $formData = new \stdClass();
        $formData->email = '';

        $form = $this->createFormBuilder($formData)
            ->add('email', TextType::class, [
                'trim' => true,
                'attr' => [
                    'autofocus' => true,
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'login.reset_password_button',
                'attr' => [
                    'class' => 'ui primary button',
                ],
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['emailCanonical' => $formData->email]);
            if (null !== $user) {
                $this->mailSender->sendResetPasswordMail($user);
            }

            $this->addFlash('success', 'login.reset_password.sent_message');
        }

        return $this->render(
            'security/request-reset-password.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    public function resetPasswordAction(
        Request $request,
        string $mb64,
        string $ts,
        string $token,
    ): Response {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['emailCanonical' => \base64_decode($mb64)]);
        if (null === $user) {
            $this->addFlash('error', 'login.reset_password.link_not_valid');
            return $this->redirectToRoute('app_manage_request_reset_password');
        }

        if (false === $this->hmacCalculator->verify(['uid' => (string) $user->getId(), 'ts' => $ts, 'oldPassword' => $user->getPassword() ?? ''], $token)) {
            $this->addFlash('error', 'login.reset_password.link_not_valid');
            return $this->redirectToRoute('app_manage_request_reset_password');
        }

        if ((int) $ts < (\time() - self::MAX_RESET_LINK_AGE)) {
            $this->addFlash('error', 'login.reset_password.link_not_valid_any_more');
            return $this->redirectToRoute('app_manage_request_reset_password');
        }

        $formData = new \stdClass();
        $formData->email = $user->getEmailCanonical();
        $formData->newPassword = '';
        $formData->newPasswordRepeat = '';

        $form = $this->createFormBuilder($formData)
            ->add('email', HiddenType::class, [
                'attr' => [
                    'autofocus' => true,
                ],
            ])
            ->add('newPassword', PasswordType::class, [
                'attr' => [
                    'autofocus' => true,
                ],
            ])
            ->add('newPasswordRepeat', PasswordType::class, [
                'attr' => [
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'login.set_new_password_button',
                'attr' => [
                    'class' => 'ui primary button',
                ],
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['emailCanonical' => $formData->email]);
            if (null === $user) {
                $this->addFlash('error', 'login.reset_password.generic_error');
                return $this->redirectToRoute('app_manage_request_reset_password');
            }

            $encodedPassword = $this->passwordHasher->hashPassword($user, $formData->newPassword);
            $user->setPassword($encodedPassword);
            $this->entityManager->flush();

            $this->addFlash('success', 'login.reset_password.success');
            return $this->redirectToRoute('app_manage_login');
        }

        return $this->render(
            'security/reset-password.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
