<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

readonly class FOSUserMailer implements MailerInterface
{
    public function __construct(
        private \Symfony\Component\Mailer\MailerInterface $mailer,
        private Environment $twig,
        private UrlGeneratorInterface $router,
        private string $senderMail,
        private string $senderName,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function sendConfirmationEmailMessage(UserInterface $user): void
    {
        $template = '@FOSUser/Registration/email.txt.twig';
        $url = $this->router->generate('fos_user_registration_confirm', ['token' => $user->getConfirmationToken()], UrlGeneratorInterface::ABSOLUTE_URL);

        $context = [
            'user' => $user,
            'confirmationUrl' => $url,
        ];

        $this->sendMessage($template, $context, (string) $user->getEmail());
    }

    /**
     * {@inheritdoc}
     */
    public function sendResettingEmailMessage(UserInterface $user): void
    {
        $template = '@FOSUser/Resetting/email.txt.twig';
        $url = $this->router->generate('fos_user_resetting_reset', ['token' => $user->getConfirmationToken()], UrlGeneratorInterface::ABSOLUTE_URL);

        $context = [
            'user' => $user,
            'confirmationUrl' => $url,
        ];

        $this->sendMessage($template, $context, (string) $user->getEmail());
    }

    /**
     * @param mixed[] $context
     */
    protected function sendMessage(string $templateName, array $context, string $toEmail): void
    {
        $template = $this->twig->load($templateName);
        $subject = $template->renderBlock('subject', $context);
        $textBody = $template->renderBlock('body_text', $context);

        $message = new Email();
        $message
            ->subject($subject)
            ->from(new Address($this->senderMail, $this->senderName))
            ->to($toEmail)
            ->text($textBody);

        if ($template->hasBlock('body_html', $context)) {
            $message->html($template->renderBlock('body_html', $context));
        }

        $this->mailer->send($message);
    }
}
