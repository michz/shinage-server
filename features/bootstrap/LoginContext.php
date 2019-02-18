<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace shinage\server\behat;

use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class LoginContext extends \Behat\MinkExtension\Context\RawMinkContext
{
    /** @var UserManagerInterface */
    private $userManager;

    /** @var string */
    private $firewallName;

    /** @var mixed */
    private $session;

    /**
     * @param mixed $session
     */
    public function __construct(
        UserManagerInterface $userManager,
        $session,
        string $firewallName
    ) {
        $this->userManager = $userManager;
        $this->session = $session;
        $this->firewallName = $firewallName;
    }

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
        $current = str_replace($this->getMinkParameter('base_url'), '', $current);
        if (false !== strpos($current, '#')) {
            $current = substr($current, 0, strpos($current, '#'));
        }

        if ($current !== $url) {
            throw new \Exception('I am on wrong page. I am on "' . $current . '", but I should be on "' . $url . '".');
        }
    }

    /**
     * @Given /^I should see an error flash message$/
     */
    public function iShouldSeeAnErrorFlashMessage(): void
    {
        $elem = $this->getSession()->getPage()->find('css', '.flash-error');
        if (null === $elem) {
            throw new \Exception('I cannot see error message.');
        }
    }

    /**
     * @Given /^I am logged in as user "([^"]*)"$/
     */
    public function iAmLoggedInAsUser(string $username): void
    {
        $driver = $this->getSession()->getDriver();
        if (!($driver instanceof BrowserKitDriver)) {
            throw new UnsupportedDriverActionException('This step is only supported by the BrowserKitDriver', $driver);
        }

        $client = $driver->getClient();
        $client->getCookieJar()->set(new Cookie(session_name(), '1'));

        $firewall = $this->firewallName;
        $user = $this->userManager->findUserBy(['username' => $username]);
        /** @var \FOS\UserBundle\Model\UserInterface $user */
        $token = new UsernamePasswordToken($user, null, $firewall, $user->getRoles());
        $this->session->set('_security_' . $firewall, serialize($token));
        $this->session->save();

        $cookie = new Cookie($this->session->getName(), $this->session->getId());
        $client->getCookieJar()->set($cookie);
    }
}
