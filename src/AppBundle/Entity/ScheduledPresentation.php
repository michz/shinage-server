<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 21.12.16
 * Time: 13:46
 */

namespace AppBundle\Entity;

/**
 * AppBundle\Entity\ScheduledPresentation
 */
class ScheduledPresentation
{
    /** @var int */
    private $id;

    /** @var Screen */
    private $screen;

    /** @var Presentation */
    private $presentation;

    /** @var \DateTime */
    private $scheduled_start;

    /** @var \DateTime */
    private $scheduled_end;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set screen
     *
     * @param Screen $screen
     *
     * @return ScheduledPresentation
     */
    public function setScreen(Screen $screen = null)
    {
        $this->screen = $screen;

        return $this;
    }

    /**
     * Get screen
     *
     * @return Screen
     */
    public function getScreen()
    {
        return $this->screen;
    }

    /**
     * Set scheduledStart
     *
     * @param \DateTime $scheduledStart
     *
     * @return ScheduledPresentation
     */
    public function setScheduledStart($scheduledStart)
    {
        $this->scheduled_start = $scheduledStart;

        return $this;
    }

    /**
     * Get scheduledStart
     *
     * @return \DateTime
     */
    public function getScheduledStart()
    {
        return $this->scheduled_start;
    }

    /**
     * Set scheduledEnd
     *
     * @param \DateTime $scheduledEnd
     *
     * @return ScheduledPresentation
     */
    public function setScheduledEnd($scheduledEnd)
    {
        $this->scheduled_end = $scheduledEnd;

        return $this;
    }

    /**
     * Get scheduledEnd
     *
     * @return \DateTime
     */
    public function getScheduledEnd()
    {
        return $this->scheduled_end;
    }

    /**
     * Set presentation
     *
     * @param Presentation $presentation
     *
     * @return ScheduledPresentation
     */
    public function setPresentation(Presentation $presentation = null)
    {
        $this->presentation = $presentation;

        return $this;
    }

    /**
     * Get presentation
     *
     * @return Presentation
     */
    public function getPresentation()
    {
        return $this->presentation;
    }
}
