<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

class ConfirmationTokenGenerator implements ConfirmationTokenGeneratorInterface
{
    public function generateConfirmationToken(): string
    {
        return \rtrim(\strtr(\base64_encode(\random_bytes(32)), '+/', '-_'), '=');
    }
}
