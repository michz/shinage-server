<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Command;

use App\Entity\User;
use App\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateAdminUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('users:create-admin')
            ->addOption(
                'plain-password',
                null,
                InputOption::VALUE_REQUIRED,
                'Plain password of the new user to create (NOT RECOMMENDED)',
                '',
            )
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'Email address of the new user to create',
            )
            ->setDescription('Creates a new admin user with all privileges');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (string) $input->getArgument('email');

        $helper = $this->getHelper('question');
        if (false === ($helper instanceof QuestionHelper)) {
            throw new \RuntimeException('An internal error occurred: Returned helper is not a QuestionHelper');
        }

        $question = new Question('Password: ', '');
        $question->setHidden(true);
        $question->setHiddenFallback(false);

        $plainPassword = (string) $input->getOption('plain-password');
        if (false === empty($plainPassword)) {
            $output->writeln('Warning: Providing a password on command line is insecure and not recommended.');
            $password = $plainPassword;
        } else {
            $password = $helper->ask($input, $output, $question);
        }

        // @TODO Introduce and verify password security
        if (empty($password)) {
            $output->writeln('Password cannot be empty.');
            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setEnabled(true);
        $user->setUserType(UserType::USER_TYPE_USER);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->addRole('ROLE_SUPER_ADMIN');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return 0;
    }
}
