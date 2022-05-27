<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

class GenericCodeGenerator implements GenericCodeGeneratorInterface
{
    public function generate(int $length): string
    {
        $chars = 'abcdefghkmnpqrstuvwxyz23456789';
        $chars_n = \strlen($chars);
        $code = '';

        for ($i = 0; $i < $length; ++$i) {
            $code .= $chars[\random_int(0, $chars_n - 1)];
        }

        return $code;
    }
}
