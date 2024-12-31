<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

class MailerTestTransport implements TransportInterface
{
    /** @var SentMessage[] */
    protected static array $mails = [];

    public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
    {
        if (null === $envelope) {
            if ($message instanceof Email) {
                $envelope = new Envelope(
                    $message->getSender() ?? $message->getFrom()[0],
                    \array_merge(
                        $message->getTo(),
                        $message->getCC(),
                        $message->getBcc(),
                    )
                );
            }
        }
        $sentMessage = new SentMessage($message, $envelope);
        self::$mails[] = $sentMessage;
        return $sentMessage;
    }

    public function reset(): void
    {
        self::$mails = [];
    }

    /**
     * @return SentMessage[]
     */
    public function getMails(): array
    {
        return self::$mails;
    }

    public function count(): int
    {
        return \count(self::$mails);
    }

    public function __toString(): string
    {
        return 'testonly';
    }
}
