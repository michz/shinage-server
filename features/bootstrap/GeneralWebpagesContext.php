<?php

namespace shinage\server\behat;

/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  20.11.17
 * @time     :  20:01
 */

class GeneralWebpagesContext extends \Behat\MinkExtension\Context\RawMinkContext
{

    /**
     * @Given /^I see a headline "([^"]*)"$/
     */
    public function iSeeAHeadline($title)
    {
        $el = $this->getSession()->getPage()->find(
            'xpath',
            '//h1[contains(., "'.$title.'")]|//h2[contains(., "'.$title.'")]|//h3[contains(., "'.$title.'")]'
        );
        if ($el === null) {
            throw new \Exception('No headline "'.$title.'" found.');
        }
    }
}
