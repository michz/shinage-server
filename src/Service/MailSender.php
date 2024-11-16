<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

readonly class MailSender implements MailSenderInterface
{
    public function __construct(
        private TranslatorInterface $translator,
        private MailerInterface $mailer,
        private Environment $twig,
        private HmacCalculatorInterface $hmacCalculator,
        private RouterInterface $router,
        private string $mailSenderMail,
        private string $mailSenderName,
    ) {
    }

    public function sendResetPasswordMail(User $user): void
    {
        $mb64 = \base64_encode($user->getEmailCanonical());
        $ts = (string) \time();
        $hmac = $this->hmacCalculator->calculate(['uid' => $user->getId(), 'ts' => $ts, 'oldPassword' => $user->getPassword()]);

        $resetLink = $this->router->generate(
            'app_manage_reset_password',
            [
                'mb64' => $mb64,
                'ts' => $ts,
                'token' => $hmac,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        $message = new Email();
        $message
            ->subject($this->translator->trans('SubjectResetPasswordMail'))
            ->from(new Address($this->mailSenderMail, $this->mailSenderName))
            ->to($user->getEmail())
            ->html(
                $this->twig->render(
                    'mail/html/security/reset_password.html.twig',
                    ['user' => $user, 'link' => $resetLink]
                )
            )
            ->text(
                $this->twig->render(
                    'mail/text/security/reset_password.txt.twig',
                    ['user' => $user, 'link' => $resetLink]
                )
            );
        $this->mailer->send($message);
    }
}
