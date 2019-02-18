<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

use App\Entity\User;

interface FilePoolPermissionCheckerInterface
{
    public function mayUserAccessRoot(User $user, string $root): bool;

    public function mayUserAccessPath(User $user, string $path): bool;
}
