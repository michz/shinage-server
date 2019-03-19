<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Factory;

use App\Entity\RegistrationCode;
use App\Entity\User;

interface RegistrationCodeFactoryInterface
{
    public function create(?User $assignOrganization): RegistrationCode;
}
