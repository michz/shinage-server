<?php

/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  20.11.17
 * @time     :  20:01
 */

class LoginContext extends \Behat\MinkExtension\Context\RawMinkContext
{

    /**
     * @Given /^I am on page "([^"]*)"$/
     * @When /^I go to URL "([^"]*)"$/
     */
    public function iGoToURL($url)
    {
        $this->visitPath($url);
    }

    /**
     * @Then /^I see an input field with name "([^"]*)"$/
     */
    public function iSeeAnInputFieldWithName($name)
    {
        $field = $this->getSession()->getPage()->findField($name);
        if (empty($field)) {
            echo $this->getSession()->getPage()->getHtml();
            throw new \Exception('Field '.$name.' not found on page.');
        }
    }

    /**
     * @Given /^I see an button with name "([^"]*)"$/
     */
    public function iSeeAnButtonWithName($name)
    {
        $field = $this->getSession()->getPage()->findButton($name);
        if (empty($field)) {
            throw new \Exception('Button '.$name.' not found on page.');
        }
    }

    /**
     * @Given /^I fill the field "([^"]*)" with "([^"]*)"$/
     */
    public function iFillTheFieldWith($fieldName, $value)
    {
        $field = $this->getSession()->getPage()->findField($fieldName);
        $field->setValue($value);
    }

    /**
     * @When /^I click on the button "([^"]*)"$/
     */
    public function iSubmitTheLoginForm($buttonName)
    {
        $this->getSession()->getPage()->findButton($buttonName)->click();
    }

    /**
     * @Then /^I should be on page "([^"]*)"$/
     * @Then /^I should be on page "([^"]*)" again$/
     */
    public function iShouldBeOnPage($url)
    {
        $current = $this->getSession()->getCurrentUrl();
        $current = str_replace($this->getMinkParameter('base_url'), '', $current);
        if ($current !== $url) {
            throw new \Exception('I am on wrong page. I am on "'.$current.'", but I should be on "'.$current.'".');
        }
    }

    /**
     * @Given /^I should see an error flash message$/
     */
    public function iShouldSeeAnErrorFlashMessage()
    {
        $elem = $this->getSession()->getPage()->find('css', '.flash-error');
        if ($elem === null) {
            throw new \Exception('I cannot see error message.');
        }
    }
}
