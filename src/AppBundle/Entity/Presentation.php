<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 05.01.2017
 * Time: 11:29
 */


namespace AppBundle\Entity;

/**
 * AppBundle\Entity\Presentation
 */
class Presentation implements \JsonSerializable
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $title = 'Presentation';

    /** @var string */
    protected $notes = '';

    /** @var string */
    protected $settings = '';

    /** @var int */
    protected $lastModified = 0;

    /** @var User */
    protected $owner;

    /** @var \Doctrine\Common\Collections\ArrayCollection */
    protected $slides;


    /**
     * @brief  Returns serialized entity.
     * @return array
     */
    public function jsonSerialize()
    {
        $slides_a = array();
        $slides = $this->getSlides();


        foreach ($slides as $slide) {
            $slides_a[] = ($slide);
        }


        return array(
            'id'        => $this->id,
            'title'     => $this->title,
            'notes'     => $this->notes,
            'settings'  => $this->settings,
            'slides'    => $slides_a
        );
    }


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
     * Set title
     *
     * @param string $title
     *
     * @return Presentation
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return Presentation
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set settings
     *
     * @param string $settings
     *
     * @return Presentation
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * Get settings
     *
     * @return string
     */
    public function getSettings()
    {
        return $this->settings;
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->slides = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * @param integer $lastModified
     */
    public function setLastModified($lastModified)
    {
        $this->lastModified = $lastModified;
    }

    /**
     * Add slide
     *
     * @param \AppBundle\Entity\Slide $slide
     *
     * @return Presentation
     */
    public function addSlide(\AppBundle\Entity\Slide $slide)
    {
        $this->slides[] = $slide;

        return $this;
    }

    /**
     * Remove slide
     *
     * @param \AppBundle\Entity\Slide $slide
     */
    public function removeSlide(\AppBundle\Entity\Slide $slide)
    {
        $this->slides->removeElement($slide);
    }

    /**
     * Get slides
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSlides()
    {
        return $this->slides;
    }


    public function __toString()
    {
        return $this->getTitle();
    }

    /**
     * Set owner
     *
     * @param \AppBundle\Entity\User $owner
     *
     * @return Presentation
     */
    public function setOwner(\AppBundle\Entity\User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \AppBundle\Entity\User
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
