<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace shinage\server\behat;

class PresentationEditorsContext extends \Behat\MinkExtension\Context\RawMinkContext
{
    /**
     * @Then I should see the slideshow editor
     */
    public function iShouldSeeTheSlideshowEditor(): void
    {
        $div = $this->getSession()->getPage()->findById('slideshowEditor');
        if (null === $div) {
            throw new \Exception('DIV with id "slideshowEditor" not found.');
        }
    }

    /**
     * @When I follow the edit link for :title
     */
    public function iFollowTheEditLinkFor(string $title): void
    {
        $link = $this->getSession()->getPage()->find(
            'xpath',
            '//h2[contains(., "' . $title . '")]/following-sibling::div/.//a'
        );
        $link->click();
    }
}
