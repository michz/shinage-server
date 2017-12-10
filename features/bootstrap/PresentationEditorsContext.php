<?php

namespace shinage\server\behat;

/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  20.11.17
 * @time     :  20:01
 */

class PresentationEditorsContext extends \Behat\MinkExtension\Context\RawMinkContext
{
    /**
     * @Then /^I should see the slideshow editor$/
     */
    public function iShouldSeeTheSlideshowEditor()
    {
        $div = $this->getSession()->getPage()->findById('slideshowEditor');
        if ($div === null) {
            throw new \Exception('DIV with id "slideshowEditor" not found.');
        }
    }

    /**
     * @When /^I follow the edit link for "([^"]*)"$/
     */
    public function iFollowTheEditLinkFor($title)
    {
        $link = $this->getSession()->getPage()->find(
            'xpath',
            '//h2[contains(., "'.$title.'")]/following-sibling::div/.//a'
        );
        $link->click();
    }
}
