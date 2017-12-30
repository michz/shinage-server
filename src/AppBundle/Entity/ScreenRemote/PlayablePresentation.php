<?php
/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  25.06.17
 * @time     :  09:16
 */

namespace AppBundle\Entity\ScreenRemote;

class PlayablePresentation
{
    /** @var PlayablePresentationSettings */
    public $settings;

    /** @var PlayablePresentationSlide[]  */
    public $slides = [];

    /** @var int */
    public $lastModified = 0;

    /**
     * PlayablePresentation constructor.
     */
    public function __construct()
    {
        $this->settings = new PlayablePresentationSettings();
    }
}
