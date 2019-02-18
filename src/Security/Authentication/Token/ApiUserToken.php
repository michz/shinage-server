<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class ApiUserToken extends AbstractToken
{
    /**
     * @param array|string[] $roles
     */
    public function __construct(array $roles = [])
    {
        parent::__construct($roles);

        // If the user has roles, consider it authenticated
        $this->setAuthenticated(count($roles) > 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials()
    {
        return '';
    }
}
