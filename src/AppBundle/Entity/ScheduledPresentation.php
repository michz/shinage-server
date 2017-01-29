<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 21.12.16
 * Time: 13:46
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AppBundle\Entity\ScheduledPresentation
 *
 * @ORM\Entity
 * @ORM\Table(name="schedule")
 */
class ScheduledPresentation
{

    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Screen", fetch="EAGER")
     * @ORM\JoinColumn(name="screen_id", referencedColumnName="guid", nullable=false)
     */
    private $screen;

    /**
     * @ORM\ManyToOne(targetEntity="Presentation", fetch="EAGER")
     * @ORM\JoinColumn(name="presentation_id", referencedColumnName="id", nullable=false)
     */
    private $presentation;

    /**
     * @ORM\Column(type="datetime")
     */
    private $scheduled_start;

    /**
     * @ORM\Column(type="datetime")
     */
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
     * @param \AppBundle\Entity\Screen $screen
     *
     * @return ScheduledPresentation
     */
    public function setScreen(\AppBundle\Entity\Screen $screen = null)
    {
        $this->screen = $screen;

        return $this;
    }

    /**
     * Get screen
     *
     * @return \AppBundle\Entity\Screen
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
     * @param \AppBundle\Entity\Presentation $presentation
     *
     * @return ScheduledPresentation
     */
    public function setPresentation(\AppBundle\Entity\Presentation $presentation = null)
    {
        $this->presentation = $presentation;

        return $this;
    }

    /**
     * Get presentation
     *
     * @return \AppBundle\Entity\Presentation
     */
    public function getPresentation()
    {
        return $this->presentation;
    }
}
