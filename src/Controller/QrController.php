<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller;

use App\Entity\Screen;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\BuilderRegistryInterface;
use Endroid\QrCodeBundle\Response\QrCodeResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class QrController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RouterInterface $router,
        private readonly BuilderRegistryInterface $builderRegistry,
    ) {
    }

    public function registerScreenUrlAction(string $connectCode): Response
    {
        $screen = $this->entityManager->getRepository(Screen::class)->findOneBy(['connect_code' => $connectCode]);
        if (empty($screen)) {
            throw new NotFoundHttpException();
        }

        $builder = $this->builderRegistry->getBuilder('default');
        $result = $builder
            ->data($this->router->generate('management-screens', [], UrlGeneratorInterface::ABSOLUTE_URL) .
                '?connect_code=' . $screen->getConnectCode())
            ->build();

        return new QrCodeResponse($result);
    }
}
