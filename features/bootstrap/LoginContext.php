<?php

namespace shinage\server\behat;

use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  20.11.17
 * @time     :  20:01
 */

class LoginContext extends \Behat\MinkExtension\Context\RawMinkContext
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var UserManagerInterface */
    private $userManager;

    /** @var string */
    private $firewallName;

    private $session;

    /**
     * LoginContext constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param UserManagerInterface   $userManager
     * @param string                 $firewallName
     * @param                        $session
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserManagerInterface $userManager,
        $session,
        string $firewallName
    ) {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->session = $session;
        $this->firewallName = $firewallName;
    }


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


    /**
     * @Given /^I am logged in as user "([^"]*)"$/
     */
    public function iAmLoggedInAsUser($username)
    {
        $driver = $this->getSession()->getDriver();
        if (! ($driver instanceof BrowserKitDriver)) {
            throw new UnsupportedDriverActionException('This step is only supported by the BrowserKitDriver', $driver);
        }

        $client = $driver->getClient();
        $client->getCookieJar()->set(new Cookie(session_name(), true));

        $firewall = $this->firewallName;
        $user = $this->userManager->findUserBy(['username' => $username]);
        /** @var \FOS\UserBundle\Model\UserInterface $user */
        $token = new UsernamePasswordToken($user, null, $firewall, $user->getRoles());
        $this->session->set('_security_'.$firewall, serialize($token));
        $this->session->save();

        $cookie = new Cookie($this->session->getName(), $this->session->getId());
        $client->getCookieJar()->set($cookie);
    }
}
