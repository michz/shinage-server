<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Factory;

use App\Entity\RegistrationCode;
use App\Entity\User;
use App\Service\GenericCodeGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RegistrationCodeFactory implements RegistrationCodeFactoryInterface
{
    const CODE_LENGTH = 12;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var GenericCodeGeneratorInterface */
    private $genericCodeGenerator;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        GenericCodeGeneratorInterface $genericCodeGenerator
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->genericCodeGenerator = $genericCodeGenerator;
    }

    public function create(?User $assignOrganization): RegistrationCode
    {
        $now = new \DateTime();
        $validUntil = new \DateTime();
        $validUntil->add(new \DateInterval('P99Y'));

        $code = new RegistrationCode();
        $code->setAssignOrganization($assignOrganization);
        $code->setCreatedDate($now);

        $user = $this->tokenStorage->getToken()->getUser();
        if (\is_a($user, User::class)) {
            $code->setCreatedBy($user);
        }

        $code->setValidUntil($validUntil);
        $code->setCode($this->genericCodeGenerator->generate(self::CODE_LENGTH));

        return $code;
    }
}
