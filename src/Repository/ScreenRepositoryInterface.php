<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Repository;

use App\Entity\Screen;
use App\Entity\User;

interface ScreenRepositoryInterface
{
    /**
     * @return Screen[]
     */
    public function getScreensForUser(User $user): array;

    public function getScreenByConnectCode(string $connectCode): ?Screen;
}
