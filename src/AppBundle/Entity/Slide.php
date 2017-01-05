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
 * AppBundle\Entity\Slide
 *
 * @ORM\Entity
 * @ORM\Table(name="slides")
 */
class Slide implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $slide_type = 'image';

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $duration = 0;

    /**
     * @ORM\Column(type="text")
     */
    protected $notes = '';

    /**
     * @ORM\Column(type="text")
     */
    protected $file_path = '';

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $sort_order = 0;

    /**
     * @ORM\ManyToOne(targetEntity="Presentation", inversedBy="slides")
     * @ORM\JoinColumn(name="presentation_id", referencedColumnName="id", nullable=false)
     */
    protected $presentation;


    /**
     * @brief  Returns serialized entity.
     * @return array
     */
    public function jsonSerialize()
    {
        return array(
            'id'        => $this->id,
            'slide_type'=> $this->slide_type,
            'duration'  => $this->duration,
            'notes'     => $this->notes,
            'file_path' => $this->file_path,
            'sort_order'=> $this->sort_order
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
     * Set slideType
     *
     * @param string $slideType
     *
     * @return Slide
     */
    public function setSlideType($slideType)
    {
        $this->slide_type = $slideType;

        return $this;
    }

    /**
     * Get slideType
     *
     * @return string
     */
    public function getSlideType()
    {
        return $this->slide_type;
    }

    /**
     * Set duration
     *
     * @param integer $duration
     *
     * @return Slide
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration
     *
     * @return integer
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return Slide
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
     * Set filePath
     *
     * @param string $filePath
     *
     * @return Slide
     */
    public function setFilePath($filePath)
    {
        $this->file_path = $filePath;

        return $this;
    }

    /**
     * Get filePath
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->file_path;
    }

    /**
     * Set presentation
     *
     * @param \AppBundle\Entity\Presentation $presentation
     *
     * @return Slide
     */
    public function setPresentation(\AppBundle\Entity\Presentation $presentation)
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

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     *
     * @return Slide
     */
    public function setSortOrder($sortOrder)
    {
        $this->sort_order = $sortOrder;

        return $this;
    }

    /**
     * Get sortOrder
     *
     * @return integer
     */
    public function getSortOrder()
    {
        return $this->sort_order;
    }
}
