<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Api\v1;

use App\Controller\Api\Exception\AccessDeniedException;
use App\Entity\Presentation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PresentationsController extends AbstractController
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var SerializerInterface */
    private $serializer;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    public function listAction(): Response
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

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
            $this->serializer->serialize($presentations, 'json'),
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
            $this->serializer->serialize($presentation, 'json'),
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

        $user = $this->tokenStorage->getToken()->getUser();
        if (false === ($user instanceof User)) {
            throw new AccessDeniedException('User could not be loaded to be set as owner.');
        }

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
        return new Response('', 204);
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
