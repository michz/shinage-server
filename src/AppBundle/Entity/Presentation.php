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
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    protected $owner_user;

    /**
     * @ORM\ManyToOne(targetEntity="Organization")
     * @ORM\JoinColumn(name="orga_id", referencedColumnName="id", nullable=true)
     */
    protected $owner_orga;

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
     * Set ownerUser
     *
     * @param \AppBundle\Entity\User $ownerUser
     *
     * @return Presentation
     */
    public function setOwnerUser(\AppBundle\Entity\User $ownerUser = null)
    {
        $this->owner_user = $ownerUser;

        return $this;
    }

    /**
     * Get ownerUser
     *
     * @return \AppBundle\Entity\User
     */
    public function getOwnerUser()
    {
        return $this->owner_user;
    }

    /**
     * Set ownerOrga
     *
     * @param \AppBundle\Entity\Organization $ownerOrga
     *
     * @return Presentation
     */
    public function setOwnerOrga(\AppBundle\Entity\Organization $ownerOrga = null)
    {
        $this->owner_orga = $ownerOrga;

        return $this;
    }

    /**
     * Get ownerOrga
     *
     * @return \AppBundle\Entity\Organization
     */
    public function getOwnerOrga()
    {
        return $this->owner_orga;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->slides = new \Doctrine\Common\Collections\ArrayCollection();
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
}
