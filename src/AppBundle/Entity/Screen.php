<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 20.12.16
 * Time: 17:31
 */


namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AppBundle\Entity\Screen
 *
 * @ORM\Entity
 * @ORM\Table(name="screens")
 */
class Screen
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=36)
     */
    private $guid;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $name = 'unbenannte Anzeige';

    /**
     * @ORM\Column(type="text")
     */
    private $location = '';

    /**
     * @ORM\Column(type="text")
     */
    private $notes = '';

    /**
     * @ORM\Column(type="text")
     */
    private $admin_c = '';

    /**
     * @ORM\Column(type="datetime")
     */
    private $first_connect;

    /**
     * @ORM\Column(type="datetime")
     */
    private $last_connect;

    /**
     * @ORM\Column(type="string", length=8)
     */
    private $connect_code = '';

    /**
     * @ORM\ManyToOne(targetEntity="Presentation")
     * @ORM\JoinColumn(name="presentation_id", referencedColumnName="id")
     */
    protected $default_presentation;

    /**
     * Set guid
     *
     * @param string $guid
     *
     * @return Screen
     */
    public function setGuid($guid)
    {
        $this->guid = $guid;

        return $this;
    }

    /**
     * Get guid
     *
     * @return string
     */
    public function getGuid()
    {
        return $this->guid;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Screen
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set location
     *
     * @param string $location
     *
     * @return Screen
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return Screen
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
     * Set adminC
     *
     * @param string $adminC
     *
     * @return Screen
     */
    public function setAdminC($adminC)
    {
        $this->admin_c = $adminC;

        return $this;
    }

    /**
     * Get adminC
     *
     * @return string
     */
    public function getAdminC()
    {
        return $this->admin_c;
    }

    /**
     * Set firstConnect
     *
     * @param \DateTime $firstConnect
     *
     * @return Screen
     */
    public function setFirstConnect($firstConnect)
    {
        $this->first_connect = $firstConnect;

        return $this;
    }

    /**
     * Get firstConnect
     *
     * @return \DateTime
     */
    public function getFirstConnect()
    {
        return $this->first_connect;
    }

    /**
     * Set lastConnect
     *
     * @param \DateTime $lastConnect
     *
     * @return Screen
     */
    public function setLastConnect($lastConnect)
    {
        $this->last_connect = $lastConnect;

        return $this;
    }

    /**
     * Get lastConnect
     *
     * @return \DateTime
     */
    public function getLastConnect()
    {
        return $this->last_connect;
    }

    /**
     * Set connectCode
     *
     * @param string $connectCode
     *
     * @return Screen
     */
    public function setConnectCode($connectCode)
    {
        $this->connect_code = $connectCode;

        return $this;
    }

    /**
     * Get connectCode
     *
     * @return string
     */
    public function getConnectCode()
    {
        return $this->connect_code;
    }

    /**
     * Set default presentation
     *
     * @param \AppBundle\Entity\Presentation $presentation
     *
     * @return Screen
     */
    public function setDefaultPresentation(\AppBundle\Entity\Presentation $presentation)
    {
        $this->default_presentation = $presentation;

        return $this;
    }

    /**
     * Get default presentation
     *
     * @return \AppBundle\Entity\Presentation
     */
    public function getDefaultPresentation()
    {
        return $this->default_presentation;
    }

}
