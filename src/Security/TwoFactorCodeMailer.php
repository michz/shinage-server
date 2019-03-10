<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Security;

use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class TwoFactorCodeMailer implements AuthCodeMailerInterface
{
    /** @var \Swift_Mailer */
    private $mailer;

    /** @var string */
    private $senderMail;

    /** @var string|null */
    private $senderName;

    /** @var EngineInterface */
    private $engine;

    public function __construct(
        \Swift_Mailer $mailer,
        EngineInterface $engine,
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
        $message = new \Swift_Message();
        $message
            ->setTo($user->getEmailAuthRecipient())
            ->setFrom([$this->senderMail => $this->senderName])
            ->setSubject('Authentication Code')
            ->setBody(
                $this->engine->render(
                    'mail/html/security/2fa_code.html.twig',
                    ['code' => $user->getEmailAuthCode()]
                ),
                'text/html'
            )
            ->addPart(
                $this->engine->render(
                    'mail/text/security/2fa_code.txt.twig',
                    ['code' => $user->getEmailAuthCode()]
                ),
                'text/plain'
            );
        $this->mailer->send($message);
    }
}
