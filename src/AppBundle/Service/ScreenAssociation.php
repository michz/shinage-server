<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 29.12.16
 * Time: 15:13
 */

namespace AppBundle\Service;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;


class ScreenAssociation
{
    var $em = null;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getScreensForUser(User $user)
    {
        $r = array();

        $rep = $this->em->getRepository('AppBundle:ScreenAssociation');
        $assoc = $rep->findBy(array('user_id' => $user->getId()));

        foreach ($assoc as $a) {
            $r[] = $a->getScreen();
        }

        return $r;
    }
}