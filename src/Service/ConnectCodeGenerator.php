<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

use App\Repository\ScreenRepositoryInterface;

readonly class ConnectCodeGenerator implements ConnectCodeGeneratorInterface
{
    public function __construct(
        private ScreenRepositoryInterface $screenRepository,
        private int $length = 8,
    ) {
    }

    public function generateUniqueConnectcode(): string
    {
        $code = '';
        $unique = false;
        while (!$unique) {
            $code = $this->generateConnectcode();

            $screen = $this->screenRepository->getScreenByConnectCode($code);
            if (null === $screen) {
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
