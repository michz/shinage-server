<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Service;

use AppBundle\Entity\User;

class FilePoolPermissionChecker
{
    public function __construct(FilePool $filePool)
    {
    }

    public function mayUserAccessRoot(User $user, string $root): bool
    {
        if ($root === 'user-' . $user->getId()) {
            return true;
        }

        foreach ($user->getOrganizations() as $organization) {
            if ($root === 'orga-' . $organization->getId()) {
                return true;
            }
        }

        return false;
    }
}
