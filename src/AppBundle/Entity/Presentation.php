<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 05.01.2017
 * Time: 11:29
 */


namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AppBundle\Entity\Presentation
 *
 * @ORM\Entity
 * @ORM\Table(name="presentations")
 */
class Presentation implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=200)
     */
    protected $title = 'Presentation';

    /**
     * @ORM\Column(type="text")
     */
    protected $notes = '';

    /**
     * @ORM\Column(type="text")
     */
    protected $settings = '';

    /**
     * @ORM\Column(type="integer", length=16)
     */
    protected $lastModified = 0;


    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    protected $owner;


    /**
     * @ORM\OneToMany(targetEntity="Slide", mappedBy="presentation")
     * @ORM\OrderBy({"sort_order" = "ASC"})
     */
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
