<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Repository;

use App\Entity\User;

interface UserRepositoryInterface
{
    /**
     * @return User[]
     */
    public function findOrganizationsByMailHost(string $host): array;
}
