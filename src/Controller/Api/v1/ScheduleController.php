<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Api\v1;

use App\Entity\Presentation;
use App\Entity\ScheduledPresentation;
use App\Entity\Screen;
use App\Entity\ScreenAssociation;
use App\Security\LoggedInUserRepository;
use App\Service\ScheduleCollisionHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ScheduleController extends AbstractController
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var SerializerInterface */
    private $serializer;

    /** @var ScheduleCollisionHandlerInterface */
    private $collisionHandler;

    /** @var LoggedInUserRepository */
    private $loggedInUserRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ScheduleCollisionHandlerInterface $collisionHandler,
        LoggedInUserRepository $loggedInUserRepository
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->collisionHandler = $collisionHandler;
        $this->loggedInUserRepository = $loggedInUserRepository;
    }

    public function listAction(): Response
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $users = $this->getAllowedUserIds();

        $queryBuilder
            ->select('sp')
            ->from(ScheduledPresentation::class, 'sp')
            ->join(
                Screen::class,
                's',
                Expr\Join::WITH,
                $queryBuilder->expr()->eq('sp.screen', 's.guid')
            )
            ->innerJoin(
                ScreenAssociation::class,
                'association',
                Expr\Join::WITH,
                $queryBuilder->expr()->eq('association.screen', 's.guid')
            )
            ->where($queryBuilder->expr()->gte('sp.scheduled_end', ':scheduled_end'))
            ->andWhere($queryBuilder->expr()->in('association.user', $users))
            ->setParameter('scheduled_end', (new \DateTime())->format('Y-m-d H:i:s'));

        $scheduledPresentations = $queryBuilder->getQuery()->execute();

        return new Response(
            $this->serializer->serialize(
                $scheduledPresentations,
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
        $scheduledPresentation = $this->entityManager->find(ScheduledPresentation::class, $id);
        if (null === $scheduledPresentation) {
            throw new NotFoundHttpException('ScheduledPresentation with id `' . $id . '` not found.');
        }

        $this->denyAccessUnlessGranted('get', $scheduledPresentation);

        return new Response(
            $this->serializer->serialize(
                $scheduledPresentation,
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
        $rawBody = $request->getContent();
        $rawData = \json_decode($rawBody);

        if (!$rawBody) {
            throw new BadRequestHttpException('Request body could not be parsed as json.');
        }

        /** @var ScheduledPresentation $scheduledPresentation */
        $scheduledPresentation = $this->serializer->deserialize(
            $rawBody,
            ScheduledPresentation::class,
            'json',
            DeserializationContext::create()->setGroups(['api'])
        );

        $screen = $this->entityManager->find(Screen::class, $rawData->screen);
        if (null === $screen) {
            throw new NotFoundHttpException('The given screen could not be found.');
        }

        $this->denyAccessUnlessGranted('schedule', $screen);
        $scheduledPresentation->setScreen($screen);

        $presentation = $this->entityManager->find(Presentation::class, $rawData->presentation);
        if (null === $presentation) {
            throw new NotFoundHttpException('The given presentation could not be found.');
        }

        $this->denyAccessUnlessGranted('get', $presentation);
        $scheduledPresentation->setPresentation($presentation);

        if (false === $this->entityManager->contains($scheduledPresentation)) {
            // Persist new scheduledPresentation
            $this->entityManager->persist($scheduledPresentation);
        }

        $this->entityManager->flush();

        // detect and resolve collisions
        $this->collisionHandler->handleCollisions($scheduledPresentation);
        $this->entityManager->flush();

        return new Response(
            $this->serializer->serialize(
                $scheduledPresentation,
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
        $scheduledPresentation = $this->entityManager->find(ScheduledPresentation::class, $id);
        if (null === $scheduledPresentation) {
            throw new NotFoundHttpException('ScheduledPresentation with id `' . $id . '` not found.');
        }

        $this->denyAccessUnlessGranted('delete', $scheduledPresentation);

        $this->entityManager->remove($scheduledPresentation);
        $this->entityManager->flush();

        return new Response('', 204);
    }

    /**
     * @return int[]
     */
    private function getAllowedUserIds(): array
    {
        $user = $this->loggedInUserRepository->getLoggedInUserOrDenyAccess();

        $allowedIds = [$user->getId()];
        foreach ($user->getOrganizations() as $organization) {
            $allowedIds[] = $organization->getId();
        }

        return $allowedIds;
    }
}
