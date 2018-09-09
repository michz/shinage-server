<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Service;

use AppBundle\Entity\User;

interface FilePoolPermissionCheckerInterface
{
    public function mayUserAccessRoot(User $user, string $root): bool;

    public function mayUserAccessPath(User $user, string $path): bool;
}
