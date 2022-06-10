<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace Tests\Behat;

use App\Service\MailerTestTransport;
use Symfony\Component\Mailer\SentMessage;
use Webmozart\Assert\Assert;

class MailContext extends \Behat\MinkExtension\Context\RawMinkContext
{
    private MailerTestTransport $mailerTestTransport;

    public function __construct(
        MailerTestTransport $mailerTestTransport
    ) {
        $this->mailerTestTransport = $mailerTestTransport;
    }

    /**
     * @Then I should not see any new mails
     */
    public function iShouldNotSeeAnyNewMails(): void
    {
        Assert::eq($this->mailerTestTransport->count(), 0);
    }

    /**
     * @Then I should see a new mail to :to
     */
    public function iShouldSeeANewMailTo(string $to): void
    {
        $mails = $this->mailerTestTransport->getMails();
        /** @var SentMessage $mail */
        foreach ($mails as $mail) {
            foreach ($mail->getEnvelope()->getRecipients() as $recipient) {
                if ($recipient->getAddress() === $to) {
                    return;
                }
            }
        }

        throw new \Exception('Did not find a new mail to  ' . $to);
    }
}
