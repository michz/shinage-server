<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace Tests\Behat\Gui;

use Behat\MinkExtension\Context\RawMinkContext;

class LoginContext extends RawMinkContext
{
    /**
     * @Given /^I am on page "([^"]*)"$/
     *
     * @When /^I go to URL "([^"]*)"$/
     */
    public function iGoToURL(string $url): void
    {
        $this->visitPath($url);
    }

    /**
     * @Then /^I see an input field with name "([^"]*)"$/
     */
    public function iSeeAnInputFieldWithName(string $name): void
    {
        $field = $this->getSession()->getPage()->findField($name);
        if (empty($field)) {
            echo $this->getSession()->getPage()->getHtml();
            throw new \Exception('Field ' . $name . ' not found on page.');
        }
    }

    /**
     * @Given /^I see an button with name "([^"]*)"$/
     */
    public function iSeeAnButtonWithName(string $name): void
    {
        $field = $this->getSession()->getPage()->findButton($name);
        if (empty($field)) {
            throw new \Exception('Button ' . $name . ' not found on page.');
        }
    }

    /**
     * @Given /^I fill the field "([^"]*)" with "([^"]*)"$/
     */
    public function iFillTheFieldWith(string $fieldName, string $value): void
    {
        $field = $this->getSession()->getPage()->findField($fieldName);
        $field->setValue($value);
    }

    /**
     * @When /^I click on the button "([^"]*)"$/
     */
    public function iSubmitTheLoginForm(string $buttonName): void
    {
        $this->getSession()->getPage()->findButton($buttonName)->click();
    }

    /**
     * @Then /^I should be on page "([^"]*)"$/
     * @Then /^I should be on page "([^"]*)" again$/
     */
    public function iShouldBeOnPage(string $url): void
    {
        $current = $this->getSession()->getCurrentUrl();
        $current = \str_replace($this->getMinkParameter('base_url'), '', $current);
        if (false !== \strpos($current, '#')) {
            $current = \substr($current, 0, \strpos($current, '#'));
        }

        if ($current !== $url) {
            throw new \Exception('I am on wrong page. I am on "' . $current . '", but I should be on "' . $url . '".');
        }
    }

    /**
     * @Given /^I should see an error message$/
     */
    public function iShouldSeeAnErrorMessage(): void
    {
        $elem = $this->getSession()->getPage()->find('css', '.negative.message');
        if (null === $elem) {
            throw new \Exception('I cannot see error message.');
        }
    }

    /**
     * @Given /^I am logged in as user "([^"]*)" with password "([^"]*)"$/
     */
    public function iAmLoggedInAsUser(string $username, string $password): void
    {
        $this->iGoToURL('login');
        $this->iFillTheFieldWith('_username', $username);
        $this->iFillTheFieldWith('_password', $password);
        $this->iSubmitTheLoginForm('_submit');
    }
}
