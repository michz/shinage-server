<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Api\v1;

use App\Entity\Presentation;
use App\Security\LoggedInUserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PresentationsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
        private readonly LoggedInUserRepositoryInterface $loggedInUserRepository,
    ) {
    }

    public function listAction(): Response
    {
        $user = $this->loggedInUserRepository->getLoggedInUserOrDenyAccess();

        $queryBuilder = $this->entityManager->createQueryBuilder();

        $allowedIds = [$user->getId()];
        $allowed = [$user];
        foreach ($user->getOrganizations() as $organization) {
            $allowedIds[] = $organization->getId();
            $allowed[] = $organization;
        }

        $queryBuilder
            ->select('p')
            ->from(Presentation::class, 'p')
            ->where($queryBuilder->expr()->in('p.owner', $allowedIds));

        $presentations = $queryBuilder->getQuery()->execute();

        return new Response(
            $this->serializer->serialize(
                $presentations,
                'json',
                SerializationContext::create()->setGroups(['api'])
            ),
            200,
            [
                'Content-Type' => 'application/json; charset=UTF-8',
            ]
        );
    }

    public function getAction(int $id): Response
    {
        $presentation = $this->entityManager->find(Presentation::class, $id);
        if (null === $presentation) {
            throw new NotFoundHttpException('Presentation with id `' . $id . '` not found.');
        }

        $this->denyAccessUnlessGranted('get', $presentation);

        return new Response(
            $this->serializer->serialize(
                $presentation,
                'json',
                SerializationContext::create()->setGroups(['api'])
            ),
            200,
            [
                'Content-Type' => 'application/json; charset=UTF-8',
            ]
        );
    }

    public function putAction(Request $request): Response
    {
        /** @var Presentation $presentation */
        $presentation = $this->serializer->deserialize($request->getContent(), Presentation::class, 'json');

        $user = $this->loggedInUserRepository->getLoggedInUserOrDenyAccess();

        if (false === $this->entityManager->contains($presentation)) {
            // Persist new presentation
            $presentation->setOwner($user);
            $this->entityManager->persist($presentation);
        } else {
            // Assure that existing presentation may be altered
            $this->denyAccessUnlessGranted('put', $presentation);
        }

        $presentation->setLastModified(new \DateTime());
        $this->entityManager->flush();

        return new Response(
            $this->serializer->serialize(
                $presentation,
                'json',
                SerializationContext::create()->setGroups(['api'])
            ),
            200,
            [
                'Content-Type' => 'application/json; charset=UTF-8',
            ]
        );
    }

    public function deleteAction(int $id): Response
    {
        $presentation = $this->entityManager->find(Presentation::class, $id);
        if (null === $presentation) {
            throw new NotFoundHttpException('Presentation with id `' . $id . '` not found.');
        }

        $this->denyAccessUnlessGranted('delete', $presentation);

        $this->entityManager->remove($presentation);
        $this->entityManager->flush();

        return new Response('', 204);
    }
}
