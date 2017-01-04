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

use AppBundle\Entity\Screen;


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

        // get organizations for user
        $orgas = $user->getOrganizations();

        foreach ($orgas as $o) {
            $assoc = $rep->findBy(array('orga_id' => $o->getId()));
            foreach ($assoc as $a) {
                $r[] = $a->getScreen();
            }
        }

        return $r;
    }

    public function isScreenAssociated(Screen $screen)
    {
        $rep = $this->em->getRepository('AppBundle:ScreenAssociation');
        $assoc = $rep->findBy(array('screen' => $screen->getGuid()));

        return (count($assoc) > 0);
    }
}