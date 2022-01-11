<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace Tests\Behat\Gui;

use App\Entity\User;
use Behat\MinkExtension\Context\RawMinkContext;
use Webmozart\Assert\Assert;

class RegistrationContext extends RawMinkContext
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

    /**
     * @Then The user :user should be in organization :organization
     */
    public function theUserShouldBeInOrganization(User $user, User $organization): void
    {
        foreach ($user->getOrganizations() as $userOrga) {
            if ($userOrga->getId() === $organization->getId()) {
                return;
            }
        }

        throw new \Exception('User is not in organization as expected.');
    }

    /**
     * @Then The user :user should not be in organization :organization
     */
    public function theUserShouldNotBeInOrganization(User $user, User $organization): void
    {
        foreach ($user->getOrganizations() as $userOrga) {
            if ($userOrga->getId() === $organization->getId()) {
                throw new \Exception('User is in organization but was expected *not* to be.');
            }
        }
    }
}
