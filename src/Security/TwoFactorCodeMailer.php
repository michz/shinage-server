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

class TwoFactorCodeMailer implements AuthCodeMailerInterface
{
    /** @var MailerInterface */
    private $mailer;

    /** @var string */
    private $senderMail;

    /** @var string|null */
    private $senderName;

    /** @var Environment */
    private $engine;

    public function __construct(
        MailerInterface $mailer,
        Environment $engine,
        string $senderMail,
        ?string $senderName
    ) {
        $this->mailer = $mailer;
        $this->senderMail = $senderMail;
        $this->senderName = $senderName;
        $this->engine = $engine;
    }

    public function sendAuthCode(TwoFactorInterface $user): void
    {
        $message = new Email();
        $message
            ->to($user->getEmailAuthRecipient())
            ->from(new Address($this->senderMail, $this->senderName))
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
