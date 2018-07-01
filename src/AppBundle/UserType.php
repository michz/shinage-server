<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle;

class UserType
{
    /*
     * ENUM('user', 'organization')
     */

    const USER_TYPE_USER    = 'user';
    const USER_TYPE_ORGA    = 'organization';
}
