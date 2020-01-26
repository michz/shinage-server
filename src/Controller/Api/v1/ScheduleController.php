<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Api\v1;

use App\Entity\Presentation;
use App\Entity\ScheduledPresentation;
use App\Entity\Screen;
use App\Repository\ScheduleRepositoryInterface;
use App\Security\LoggedInUserRepository;
use App\Security\VolatileScreenUser;
use App\Service\ScheduleCollisionHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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

    /** @var ScheduleRepositoryInterface */
    private $scheduleRepository;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ScheduleCollisionHandlerInterface $collisionHandler,
        LoggedInUserRepository $loggedInUserRepository,
        ScheduleRepositoryInterface $scheduleRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->collisionHandler = $collisionHandler;
        $this->loggedInUserRepository = $loggedInUserRepository;
        $this->scheduleRepository = $scheduleRepository;
        $this->tokenStorage = $tokenStorage;
    }

    public function listAction(Request $request): Response
    {
        $scheduledPresentationsQuery = $this->scheduleRepository->reset();

        if ($request->get('from')) {
            try {
                $from = new \DateTime($request->get('from'));
                $scheduledPresentationsQuery->addFromConstraint($from);
            } catch (\Throwable $e) {
                throw new BadRequestHttpException('Could not parse "from" parameter.');
            }
        } else {
            // If now from is given, start at "now" to reduce database load.
            $scheduledPresentationsQuery->addFromConstraint(new \DateTime());
        }

        if ($request->get('until')) {
            try {
                $until = new \DateTime($request->get('until'));
                $scheduledPresentationsQuery->addUntilConstraint($until);
            } catch (\Throwable $e) {
                throw new BadRequestHttpException('Could not parse "until" parameter.');
            }
        }

        // If identified by Screen, check only for this screen; otherwise all screens the given user is allowed
        $user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof VolatileScreenUser) {
            $scheduledPresentationsQuery->addScreenConstraint($user->getScreen());
        } else {
            $users = $this->getAllowedUserIds();
            $scheduledPresentationsQuery->addUsersByIdsConstraint($users);
        }

        $scheduledPresentations = $scheduledPresentationsQuery->getResults();

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

        $this->denyAccessUnlessGranted('schedule.put', $screen);
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
