<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management\Admin;

use App\Entity\RegistrationCode;
use App\Factory\RegistrationCodeFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InvitationCodesController extends AbstractController
{
    public const DEFAULT_CODE_LENGTH = 12;
    public const MAX_COUNT_GENERATE = 100;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RegistrationCodeFactoryInterface $registrationCodeFactory,
    ) {
    }

    public function indexAction(): Response
    {
        $codeRepo = $this->entityManager->getRepository(RegistrationCode::class);
        return $this->render('adm/invitation_codes.html.twig', [
            'codes' => $codeRepo->findAll(),
        ]);
    }

    public function createOneAction(Request $request): Response
    {
        $codeData = (string) $request->get('code');

        if (\strlen($codeData) < 7) {
            $this->addFlash('error', 'admin.codes.too_short');
        } elseif (\preg_match('/[^A-Za-z0-9\\-_]/', $codeData)) {
            $this->addFlash('error', 'admin.codes.invalid_characters');
        } else {
            try {
                $code = $this->registrationCodeFactory->create(null);
                $code->setCode($codeData);
                $this->entityManager->persist($code);
                $this->entityManager->flush();
                $this->addFlash('success', 'admin.codes.saved_code');
            } catch (\Throwable $throwable) {
                $this->addFlash('error', 'admin.codes.could_not_save');
            }
        }

        return $this->redirectToRoute('admin-invitation-codes');
    }

    public function createGenerateOneAction(): Response
    {
        try {
            $code = $this->registrationCodeFactory->create(null);
            $this->entityManager->persist($code);
            $this->entityManager->flush();
            $this->addFlash('success', 'admin.codes.generated_code');
        } catch (\Throwable $throwable) {
            $this->addFlash('error', 'admin.codes.could_not_create');
        }

        return $this->redirectToRoute('admin-invitation-codes');
    }

    public function createGenerateMultipleAction(Request $request): Response
    {
        $count = (int) $request->get('count');

        if ($count > self::MAX_COUNT_GENERATE) {
            $count = self::MAX_COUNT_GENERATE;
            $this->addFlash('error', 'admin.codes.too_many_codes_given');
        }

        for ($i = 0; $i < $count; ++$i) {
            try {
                $code = $this->registrationCodeFactory->create(null);
                $this->entityManager->persist($code);
            } catch (\Throwable $throwable) {
                $this->addFlash('error', 'admin.codes.could_not_create');
            }
        }

        try {
            $this->entityManager->flush();
            $this->addFlash('success', 'admin.codes.generated_codes');
        } catch (\Throwable $throwable) {
            $this->addFlash('error', 'admin.codes.could_not_create');
        }

        return $this->redirectToRoute('admin-invitation-codes');
    }

    public function deleteAction(string $codeData): Response
    {
        try {
            $code = $this->entityManager->find(RegistrationCode::class, $codeData);
            $this->entityManager->remove($code);
            $this->entityManager->flush();
            $this->addFlash('success', 'admin.codes.deleted');
        } catch (\Throwable $throwable) {
            $this->addFlash('error', 'admin.codes.could_not_delete');
        }

        return $this->redirectToRoute('admin-invitation-codes');
    }
}
