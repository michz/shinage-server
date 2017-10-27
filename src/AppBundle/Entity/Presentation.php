<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 05.01.2017
 * Time: 11:29
 */


namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * AppBundle\Entity\Presentation
 */
class Presentation
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


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->slides = new ArrayCollection();
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
     * @return string
     */
    public function __toString()
    {
        return $this->getTitle();
    }

    /**
     * Set owner
     *
     * @param User $owner
     *
     * @return Presentation
     */
    public function setOwner(User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
