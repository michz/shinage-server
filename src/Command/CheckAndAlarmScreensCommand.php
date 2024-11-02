<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Command;

use App\Entity\Screen;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class CheckAndAlarmScreensCommand extends Command
{
    public const MAIL_EOL = "\r\n";

    private EntityManagerInterface $entityManager;

    private MailerInterface $mailer;

    private string $senderMail;

    private string $senderName;

    public function __construct(
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        string $senderMail,
        string $senderName,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->senderMail = $senderMail;
        $this->senderName = $senderName;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('screens:check-and-alarm')
            ->addOption(
                'dry-run',
                'd',
                InputOption::VALUE_NONE,
                'If enabled do not send any alarms, just list them on stdout'
            )
            ->setDescription('Check the last connections timestamps of the screens and send alarming mails');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dry = (bool) $input->getOption('dry-run');
        if ($dry) {
            // In dry mode verbosity must be set to at least VERBOSE
            $output->setVerbosity(\max($output->getVerbosity(), OutputInterface::VERBOSITY_VERBOSE));
            $output->writeln('<info>DRY MODE: Setting verbosity automatically to VERBOSE.</info>');
        }

        $screenRepo = $this->entityManager->getRepository(Screen::class);
        $screens = $screenRepo->findBy(['alarming_enabled' => true]);

        foreach ($screens as $screen) {
            $lastConnectTimestamp = $screen->getLastConnect()->getTimestamp();
            $criticalTimestamp = \time() - $screen->getAlarmingConnectionThreshold() * 60;
            if ($lastConnectTimestamp < $criticalTimestamp) {
                // Alarm!
                $output->writeln(
                    '<error>Screen with id ' . $screen->getGuid() . ' has not connected recently.</error>',
                    OutputInterface::VERBOSITY_VERBOSE
                );

                if (false === $dry) {
                    $this->sendAlarmByMail($screen, $output);
                }
            }
        }

        return 0;
    }

    private function sendAlarmByMail(Screen $screen, OutputInterface $output): void
    {
        $alarmingMailTargets = $screen->getAlarmingMailTargets();
        if (empty($alarmingMailTargets)) {
            $output->writeln(
                'Screen  ' . $screen->getGuid() . '  does not have any readable recipients.',
                OutputInterface::VERBOSITY_VERBOSE
            );
            return;
        }

        $recipients = \explode(';', $alarmingMailTargets);

        $body = 'ALARM! Last successful connection from your screen "' . $screen->getName() . '" was on  ' .
            $screen->getLastConnect()->format('Y-m-d H:i:s') . ' .' . self::MAIL_EOL . self::MAIL_EOL .
            'This is longer than your configured threshold of  ' . $screen->getAlarmingConnectionThreshold() .
            ' minutes  ago.' . self::MAIL_EOL;

        $message = new Email();
        $message
            ->from(new Address($this->senderMail, $this->senderName))
            ->subject('Shinage Screen Alarm: Last connection too long ago')
            ->text($body);

        foreach ($recipients as $recipient) {
            $message->addTo(Address::create($recipient));
        }

        $this->mailer->send($message);
    }
}
