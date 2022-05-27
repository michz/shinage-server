<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

use App\Entity\Screen;
use Doctrine\ORM\EntityManagerInterface;

class ConnectCodeGenerator implements ConnectCodeGeneratorInterface
{
    private EntityManagerInterface $entityManager;

    private int $length;

    public function __construct(
        EntityManagerInterface $entityManager,
        int $length = 8
    ) {
        $this->entityManager = $entityManager;
        $this->length = $length;
    }

    public function generateUniqueConnectcode(): string
    {
        $rep = $this->entityManager->getRepository(Screen::class);

        $code = '';
        $unique = false;
        while (!$unique) {
            $code = $this->generateConnectcode();

            $screens = $rep->findBy(['connect_code' => $code]);
            if (0 === \count($screens)) {
                $unique = true;
            }
        }

        return $code;
    }

    protected function generateConnectcode(): string
    {
        $chars = 'abcdefghkmnpqrstuvwxyz23456789';
        $chars_n = \strlen($chars);
        $code = '';

        for ($i = 0; $i < $this->length; ++$i) {
            $code .= $chars[\random_int(0, $chars_n - 1)];
        }

        return $code;
    }
}
