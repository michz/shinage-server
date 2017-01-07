<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 29.12.16
 * Time: 15:13
 */

namespace AppBundle\Service;

use AppBundle\Entity\Organization;
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

    public function isUserAllowed(Screen $screen, User $user) {
        $rep = $this->em->getRepository('AppBundle:ScreenAssociation');
        $assoc = $rep->findBy(array('screen' => $screen->getGuid()));
        $orgas = $user->getOrganizations();

        foreach ($assoc as $a) { /** @var \AppBundle\Entity\ScreenAssociation $a */
            if ($a->getUserId() == $user) return true;

            foreach ($orgas as $o) { /** @var Organization $o */
                if ($a->getOrgaId() == $o) return true;
            }
        }

        return false;
    }

    public function isScreenAssociated(Screen $screen)
    {
        $rep = $this->em->getRepository('AppBundle:ScreenAssociation');
        $assoc = $rep->findBy(array('screen' => $screen->getGuid()));

        return (count($assoc) > 0);
    }
}