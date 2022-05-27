<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace Tests\Behat\Transform;

use Behat\Behat\Context\Context;

class DateTimeTransformerContext implements Context
{
    /**
     * @Transform :until
     * @Transform :date
     * @Transform :datetime
     */
    public function getUser(string $datetimestring): \DateTime
    {
        return new \DateTime($datetimestring);
    }
}
