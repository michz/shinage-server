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
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ConfirmationTokenGeneratorInterface */
    private $confirmationTokenGenerator;

    /** @var UserManagerInterface */
    private $userManager;

    /** @var TranslatorInterface */
    private $translator;

    /** @var \Swift_Mailer */
    private $mailer;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var string */
    private $mailSenderMail;

    /** @var string */
    private $mailSenderName;

    public function __construct(
        EntityManagerInterface $entityManager,
        ConfirmationTokenGeneratorInterface $confirmationTokenGenerator,
        UserManagerInterface $userManager,
        TranslatorInterface $translator,
        \Swift_Mailer $mailer,
        UserRepositoryInterface $userRepository,
        string $mailSenderMail,
        string $mailSenderName
    ) {
        $this->entityManager = $entityManager;
        $this->confirmationTokenGenerator = $confirmationTokenGenerator;
        $this->userManager = $userManager;
        $this->translator = $translator;
        $this->mailer = $mailer;
        $this->userRepository = $userRepository;
        $this->mailSenderMail = $mailSenderMail;
        $this->mailSenderName = $mailSenderName;
    }

    public function indexAction(Request $request): \Symfony\Component\HttpFoundation\Response
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
                $oldUser = $this->userManager->findUserByEmail($user->getEmail());
                if (null !== $oldUser) {
                    $this->addFlash(
                        'error',
                        'registration.mail_already_in_use'
                    );
                } else {
                    // good news: email is not used yet, register user
                    $user->setPlainPassword($form->get('password')->getData());
                    $user->setConfirmationToken($this->confirmationTokenGenerator->generateConfirmationToken());

                    $this->assignUserToOrganizationsByMailHost($user);
                    if (null !== $registrationCode && null !== $registrationCode->getAssignOrganization()) {
                        $user->addOrganization($registrationCode->getAssignOrganization());
                    }

                    $this->userManager->updateUser($user);
                    $this->entityManager->flush();
                    $this->invalidateRegistrationCode($registrationCodeField);

                    $this->addFlash(
                        'success',
                        'registration.success'
                    );

                    $this->sendRegistrationMail($user);

                    return $this->redirectToRoute('fos_user_security_login');
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

    public function confirmAction(string $confirmationToken): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $user = $this->userManager->findUserByConfirmationToken($confirmationToken);

        if (null === $user) {
            $this->addFlash(
                'error',
                'registration.confirmation.code_not_found'
            );
            return $this->redirectToRoute('fos_user_security_login');
        }

        $user->setEnabled(true);
        $this->userManager->updateUser($user);
        $this->addFlash(
            'success',
            'success'
        );
        return $this->redirectToRoute('fos_user_security_login');
    }

    private function sendRegistrationMail(UserInterface $user): void
    {
        $message = $this->mailer->createMessage()
            ->setSubject($this->translator->trans('SubjectRegistrationMail'))
            ->setFrom([$this->mailSenderMail => $this->mailSenderName])
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    'mail/html/security/registration.html.twig',
                    ['user' => $user]
                ),
                'text/html'
            )
            ->addPart(
                $this->renderView(
                    'mail/text/security/registration.txt.twig',
                    ['user' => $user]
                ),
                'text/plain'
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
