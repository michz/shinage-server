<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace Tests\Behat\Gui;

use Behat\MinkExtension\Context\RawMinkContext;

class GeneralWebpagesContext extends RawMinkContext
{
    /**
     * @Given I see a headline :title
     */
    public function iSeeAHeadline(string $title): void
    {
        $el = $this->getSession()->getPage()->find(
            'xpath',
            '//h1[contains(., "' . $title . '")]|//h2[contains(., "' . $title . '")]|//h3[contains(., "' . $title . '")]'
        );
        if (null === $el) {
            throw new \Exception('No headline "' . $title . '" found.');
        }
    }
}
