<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Api\v1;

use App\Entity\Presentation;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PresentationsController extends AbstractController
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var SerializerInterface */
    private $serializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    public function getAction(Request $request, int $id): Response
    {
        $presentation = $this->entityManager->find(Presentation::class, $id);
        if (null === $presentation) {
            throw new NotFoundHttpException('Presentation with id `' . $id . '` not found.');
        }

        // @TODO Implement voter
        $this->denyAccessUnlessGranted('get', $presentation);

        return new Response(
            $this->serializer->serialize($presentation, 'json'),
            200
        );
    }

    public function putAction(Request $request, int $id): Response
    {
    }

    public function deleteAction(int $id): Response
    {
        $presentation = $this->entityManager->find(Presentation::class, $id);
        if (null === $presentation) {
            throw new NotFoundHttpException('Presentation with id `' . $id . '` not found.');
        }

        // @TODO Implement voter
        $this->denyAccessUnlessGranted('delete', $presentation);

        $this->entityManager->remove($presentation);
        $this->entityManager->flush();

        return new Response('', 204);
    }
}
