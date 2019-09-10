<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace shinage\server\behat;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Webmozart\Assert\Assert;

class MailContext extends \Behat\MinkExtension\Context\RawMinkContext
{
    /** @var string */
    private $spoolDir;

    public function __construct(
        string $spoolDir
    ) {
        $this->spoolDir = $spoolDir;
    }

    public function purgeSpool(): void
    {
        $filesystem = new Filesystem();
        $finder = $this->getSpooledEmails();

        /** @var File $file */
        foreach ($finder as $file) {
            $filesystem->remove($file->getRealPath());
        }
    }

    public function getSpooledEmails(): Finder
    {
        $finder = new Finder();
        $finder->files()->in($this->spoolDir);
        return $finder;
    }

    public function getMail($file): \Swift_Message
    {
        return \unserialize(\file_get_contents($file));
    }

    /**
     * @Then I should not see any new mails
     */
    public function iShouldNotSeeAnyNewMails()
    {
        $mails = $this->getSpooledEmails();
        Assert::count($mails, 0);
    }

    /**
     * @Then I should see a new mail to :to
     */
    public function iShouldSeeANewMailTo(string $to)
    {
        $mails = $this->getSpooledEmails();
        foreach ($mails as $mail) {
            /** @var \Swift_Message $mail */
            $mail = $this->getMail($mail->getRealPath());
            if (\array_key_exists($to, $mail->getTo()) ||
                \array_key_exists($to, $mail->getCc()) ||
                \array_key_exists($to, $mail->getBcc())) {
                return true;
            }
        }

        throw new \Exception('Did not find a new mail to  ' . $to);
    }
}
