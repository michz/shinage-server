<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Security;

use App\Entity\RegistrationCode;
use App\Entity\User;
use App\Repository\UserRepositoryInterface;
use App\Service\ConfirmationTokenGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ConfirmationTokenGeneratorInterface $confirmationTokenGenerator,
        private readonly TranslatorInterface $translator,
        private readonly MailerInterface $mailer,
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly string $mailSenderMail,
        private readonly string $mailSenderName,
    ) {
    }

    public function indexAction(Request $request): Response
    {
        $user = new User();

        $form = $this->createFormBuilder($user)
            ->add('registrationCode', TextType::class, ['mapped' => false])
            ->add('email', EmailType::class)
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'PasswordsMustMatch',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'PasswordAgain'],
            ])
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $registrationCodeField = $form->get('registrationCode');
            $registrationCode = $this->validateAndGetRegistrationCode($registrationCodeField);

            if ($form->isValid()) {
                // check if there is already a user with same email
                $oldUser = $this->entityManager->getRepository(User::class)->findOneBy(['emailCanonical' => $user->getEmail()]);
                if (null !== $oldUser) {
                    $this->addFlash(
                        'error',
                        'registration.mail_already_in_use'
                    );
                } else {
                    // good news: email is not used yet, register user
                    $encodedPassword = $this->userPasswordHasher->hashPassword($user, $form->get('password')->getData());
                    $user->addRole(User::ROLE_DEFAULT);
                    $user->setPassword($encodedPassword);
                    $user->setConfirmationToken($this->confirmationTokenGenerator->generateConfirmationToken());

                    $this->assignUserToOrganizationsByMailHost($user);
                    if (null !== $registrationCode && null !== $registrationCode->getAssignOrganization()) {
                        $user->addOrganization($registrationCode->getAssignOrganization());
                    }

                    $this->entityManager->persist($user);
                    $this->entityManager->flush();

                    $this->invalidateRegistrationCode($registrationCodeField);

                    $this->addFlash(
                        'success',
                        'registration.success'
                    );

                    $this->sendRegistrationMail($user);

                    return $this->redirectToRoute('app_manage_login');
                }
            } else {
                $this->addFlash(
                    'error',
                    'registration.failed'
                );
            }
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function confirmAction(string $confirmationToken): RedirectResponse
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['confirmationToken' => $confirmationToken]);

        if (null === $user) {
            $this->addFlash(
                'error',
                'registration.confirmation.code_not_found'
            );
            return $this->redirectToRoute('app_manage_login');
        }

        $user->setEnabled(true);
        $user->setConfirmationToken(null);
        $this->entityManager->flush();

        $this->addFlash(
            'success',
            'registration.confirmation.success',
        );
        return $this->redirectToRoute('app_manage_login');
    }

    private function sendRegistrationMail(User $user): void
    {
        $message = new Email();
        $message
            ->subject($this->translator->trans('SubjectRegistrationMail'))
            ->from(new Address($this->mailSenderMail, $this->mailSenderName))
            ->to($user->getEmail())
            ->html(
                $this->renderView(
                    'mail/html/security/registration.html.twig',
                    ['user' => $user]
                )
            )
            ->text(
                $this->renderView(
                    'mail/text/security/registration.txt.twig',
                    ['user' => $user]
                )
            );
        $this->mailer->send($message);
    }

    private function validateAndGetRegistrationCode(FormInterface $formElement): ?RegistrationCode
    {
        $now = new \DateTime();
        $code = $this->entityManager->find(RegistrationCode::class, $formElement->getData());
        if (null === $code) {
            $formElement->addError(
                new FormError($this->translator->trans('registration_code_invalid', [], 'validators'))
            );
        } elseif ($now > $code->getValidUntil()) {
            $formElement->addError(
                new FormError($this->translator->trans('registration_code_expired', [], 'validators'))
            );
        }

        return $code;
    }

    private function invalidateRegistrationCode(FormInterface $formElement): void
    {
        $code = $this->entityManager->find(RegistrationCode::class, $formElement->getData());
        $this->entityManager->remove($code);
        $this->entityManager->flush();
    }

    private function assignUserToOrganizationsByMailHost(User $user): void
    {
        $mailHost = \substr($user->getEmail(), \strpos($user->getEmail(), '@') + 1);
        $organizations = $this->userRepository->findOrganizationsByMailHost($mailHost);
        foreach ($organizations as $organization) {
            if ($organization->isOrgaAssignAutomaticallyByMailHost()) {
                $user->addOrganization($organization);
            }
        }
    }
}
