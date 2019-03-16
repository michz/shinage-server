<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace shinage\server\behat;

use Webmozart\Assert\Assert;

class RegistrationContext extends \Behat\MinkExtension\Context\RawMinkContext
{
    /**
     * @Then I should see that the registration was successful
     */
    public function iShouldSeeThatTheRegistrationWasSuccessful(): void
    {
        Assert::true($this->getSession()->getPage()->has('css', '.hidden.flash.success'));
    }

    /**
     * @Then I should see that the registration was not successful
     */
    public function iShouldSeeThatTheRegistrationWasNotSuccessful(): void
    {
        Assert::true($this->getSession()->getPage()->has('css', '.hidden.flash.error'));
    }
}
