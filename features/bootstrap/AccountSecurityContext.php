<?php

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace shinage\server\behat;

use Webmozart\Assert\Assert;

class AccountSecurityContext extends \Behat\MinkExtension\Context\RawMinkContext
{

    /**
     * @When I click on the `enable mail auth` button
     */
    public function iClickOnTheEnableMailAuthButton()
    {
        $this->getSession()->getPage()->find('css', '[data-qa="button-enable-2fa-mail"]')->click();
    }

    /**
     * @Then I should see that two factor authentication via totp is disabled
     */
    public function iShouldSeeThatTwoFactorAuthenticationViaTotpIsDisabled()
    {
        Assert::notNull($this->getSession()->getPage()->find('css', '[data-qa="button-enable-2fa-totp"]'));
    }

    /**
     * @Then I should see that two factor authentication via mail is disabled
     */
    public function iShouldSeeThatTwoFactorAuthenticationViaMailIsDisabled()
    {
        Assert::notNull($this->getSession()->getPage()->find('css', '[data-qa="button-enable-2fa-mail"]'));
    }

    /**
     * @Then I should see that two factor authentication via mail is enabled
     */
    public function iShouldSeeThatTwoFactorAuthenticationViaMailIsEnabled()
    {
        Assert::null($this->getSession()->getPage()->find('css', '[data-qa="button-enable-2fa-mail"]'));
        Assert::notNull($this->getSession()->getPage()->find('css', '[data-qa="button-disable-2fa-mail"]'));
    }
}
