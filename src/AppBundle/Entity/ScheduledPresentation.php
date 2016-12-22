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
class ScheduledPresentation {

    private $id;

    private $presentation;

    /**
     * @ORM\Column(type="datetime")
     */
    private $from;

    /**
     * @ORM\Column(type="datetime")
     */
    private $until;
}