<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

use App\Entity\User;

interface MailSenderInterface
{
    public function sendResetPasswordMail(User $user): void;
}
