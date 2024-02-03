<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management\Account;

use App\Entity\User;
use App\Security\LoggedInUserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SecurityController extends AbstractController
{
    public const BACKUP_CODE_COUNT = 12;
    public const BACKUP_CODE_LENGTH = 12;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var GoogleAuthenticatorInterface */
    private $googleAuthenticatorTwoFactorProvider;

    /** @var LoggedInUserRepositoryInterface */
    private $loggedInUserRepository;

    private RequestStack $requestStack;

    public function __construct(
        EntityManagerInterface $entityManager,
        GoogleAuthenticatorInterface $googleAuthenticatorTwoFactorProvider,
        LoggedInUserRepositoryInterface $loggedInUserRepository,
        RequestStack $requestStack
    ) {
        $this->entityManager = $entityManager;
        $this->googleAuthenticatorTwoFactorProvider = $googleAuthenticatorTwoFactorProvider;
        $this->loggedInUserRepository = $loggedInUserRepository;
        $this->requestStack = $requestStack;
    }

    public function indexAction(): Response
    {
        $user = $this->loggedInUserRepository->getLoggedInUserOrDenyAccess();

        return $this->render('account/security.html.twig', [
            'totp_enabled' => $user->isGoogleAuthenticatorEnabled(),
            'mail_enabled' => $user->isEmailAuthEnabled(),
            'backup_codes' => $this->getOrCreateBackupCodes($user),
        ]);
    }

    public function disableTotpAuthAction(): Response
    {
        $user = $this->loggedInUserRepository->getLoggedInUserOrDenyAccess();
        $user->setGoogleAuthenticatorSecret(null);
        $this->entityManager->flush();

        $this->addFlash('success', '2fa_totp_deactivated_successfully');
        return $this->redirectToRoute('account-security');
    }

    public function initTotpAuthAction(): Response
    {
        $user = $this->loggedInUserRepository->getLoggedInUserOrDenyAccess();

        $initForm = $this->getInitTotpForm();

        // Form was not submitted yet, so create new secret and show challange
        $newSecret = $this->googleAuthenticatorTwoFactorProvider->generateSecret();

        // Do not save yet, so do a little workaround
        $user->setGoogleAuthenticatorSecret($newSecret);
        $qrData = $this->googleAuthenticatorTwoFactorProvider->getQRContent($user);

        // Reset to old secret
        $this->entityManager->refresh($user);

        $this->requestStack->getSession()->set('newTotpSecret', $newSecret);

        return $this->render('account/2fa_totp_init.html.twig', [
            'qrData' => $qrData,
            'form' => $initForm->createView(),
        ]);
    }

    public function completeTotpAuthAction(Request $request): Response
    {
        $user = $this->loggedInUserRepository->getLoggedInUserOrDenyAccess();

        $newSecret = $this->requestStack->getSession()->get('newTotpSecret');
        if (null === $newSecret) {
            return new Response('no-secret', 500);
        }

        $initForm = $this->getInitTotpForm();
        $initForm->handleRequest($request);
        if ($initForm->isSubmitted()) {
            if ($initForm->isValid()) {
                $data = $initForm->getData();
                $code1 = $data['code1'];
                $code2 = $data['code2'];

                $user->setGoogleAuthenticatorSecret($newSecret);

                if ($code1 === $code2) {
                    // Do not allow the same code twice
                    // Problem -> reset to old secret and abort
                    $this->entityManager->refresh($user);
                    return new Response('same_code', 500);
                } elseif ($this->googleAuthenticatorTwoFactorProvider->checkCode($user, $code1) &&
                    $this->googleAuthenticatorTwoFactorProvider->checkCode($user, $code2)
                ) {
                    // Everything fine!
                    $this->entityManager->flush();
                    $this->addFlash('success', '2fa_totp_activated_successfully');
                    return new Response('OK');
                } else {
                    // Problem -> reset to old secret and abort
                    $this->entityManager->refresh($user);
                    return new Response('wrong_code', 500);
                }
            } else {
                return new Response('invalid', 500);
            }
        }

        throw new BadRequestHttpException();
    }

    private function getInitTotpForm(): FormInterface
    {
        return $this->createFormBuilder()
            ->add('code1', TextType::class, [
                'translation_domain' => 'AccountSecurity',
                'attr' => ['pattern' => '\\d+', 'autofocus' => 'autofocus'],
            ])
            ->add('code2', TextType::class, [
                'translation_domain' => 'AccountSecurity',
                'attr' => ['pattern' => '\\d+'],
            ])
            ->getForm();
    }

    public function toggleMailAuthAction(Request $request): Response
    {
        $user = $this->loggedInUserRepository->getLoggedInUserOrDenyAccess();

        if ($request->get('enable') && '1' === $request->get('enable')) {
            $user->setEmailAuthEnabled(true);
            $this->addFlash('success', '2fa_mail_activated_successfully');
        } else {
            $user->setEmailAuthEnabled(false);
            $this->addFlash('success', '2fa_mail_deactivated_successfully');
        }

        $this->entityManager->flush();

        return $this->redirectToRoute('account-security');
    }

    /**
     * @return string[]
     */
    private function getOrCreateBackupCodes(User $user): array
    {
        $flush = false;
        $backupCodes = $user->getBackupCodes() ?: [];
        while (self::BACKUP_CODE_COUNT > \count($backupCodes)) {
            $flush = true;
            $backupCodes[] = $this->createRandomBackupCode(self::BACKUP_CODE_LENGTH);
        }

        $user->setBackupCodes($backupCodes);
        if ($flush) {
            $this->entityManager->flush();
        }

        return $backupCodes;
    }

    private function createRandomBackupCode(int $length): string
    {
        $chars = '0123456789';
        $charCount = \strlen($chars);

        $code = '';
        for ($i = 0; $i < $length; ++$i) {
            $code .= $chars[\random_int(0, $charCount - 1)];
        }

        return $code;
    }
}
