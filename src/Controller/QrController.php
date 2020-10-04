<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller;

use App\Entity\Screen;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class QrController extends AbstractController
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        EntityManagerInterface $entityManager,
        RouterInterface $router
    ) {
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    public function registerScreenUrlAction(string $connectCode): Response
    {
        $screen = $this->entityManager->getRepository(Screen::class)->findOneBy(['connect_code' => $connectCode]);
        if (empty($screen)) {
            throw new NotFoundHttpException();
        }

        $qrCode = new QrCode();
        $qrCode->setSize(400);
        $qrCode->setMargin(10);

        $qrCode->setWriterByName('png');
        $qrCode->setEncoding('UTF-8');
        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH());
        $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
        $qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
        $qrCode->setLogoSize(150, 200);
        $qrCode->setValidateResult(false);

        $qrCode->setText(
            $this->router->generate('management-screens', [], UrlGeneratorInterface::ABSOLUTE_URL) .
            '?connect_code=' . $screen->getConnectCode()
        );

        $data = $qrCode->writeString();
        return new Response(
            $data,
            200,
            [
                'Content-Type' => 'image/png',
                'Content-Length' => \strlen($data),
            ]
        );
    }
}
