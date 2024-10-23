<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Security;

use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;

readonly class TwoFactorCodeMailer implements AuthCodeMailerInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $engine,
        private string $senderMail,
        private ?string $senderName,
    ) {
    }

    public function sendAuthCode(TwoFactorInterface $user): void
    {
        $message = new Email();
        $message
            ->to($user->getEmailAuthRecipient())
            ->from(new Address($this->senderMail, $this->senderName ?? ''))
            ->subject('Authentication Code')
            ->html($this->engine->render(
                'mail/html/security/2fa_code.html.twig',
                ['code' => $user->getEmailAuthCode()]
            ))
            ->text($this->engine->render(
                'mail/text/security/2fa_code.txt.twig',
                ['code' => $user->getEmailAuthCode()]
            ));
        $this->mailer->send($message);
    }
}
