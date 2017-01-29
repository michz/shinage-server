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
use AppBundle\ScreenRoleType;
use Doctrine\ORM\EntityManager;

use AppBundle\Entity\Screen;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ScreenAssociation
{
    protected $em = null;
    protected $tokenStorage = null;

    public function __construct(EntityManager $em, TokenStorage $tokenStorage)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
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

    public function isUserAllowed(Screen $screen, User $user)
    {
        $rep = $this->em->getRepository('AppBundle:ScreenAssociation');
        $assoc = $rep->findBy(array('screen' => $screen->getGuid()));
        $orgas = $user->getOrganizations();

        foreach ($assoc as $a) { /** @var \AppBundle\Entity\ScreenAssociation $a */
            if ($a->getUserId() == $user) {
                return true;
            }

            foreach ($orgas as $o) { /** @var Organization $o */
                if ($a->getOrgaId() == $o) {
                    return true;
                }
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

    /**
     * @param Screen $screen
     * @param string $owner  (format: "user:<id>" or "orga:<id>")
     * @param string $role   (from ScreenRoleType-enum)
     * @return \AppBundle\Entity\ScreenAssociation
     */
    public function associateByString(Screen $screen, $owner, $role)
    {
        $assoc = new \AppBundle\Entity\ScreenAssociation();
        $assoc->setScreen($screen);
        $assoc->setRole($role);

        $aOwner = explode(':', $owner);
        switch ($aOwner[0]) {
            case 'user':
                $assoc->setUserId($this->em->find('AppBundle:User', $aOwner[1]));
                break;
            case 'orga':
                $assoc->setOrgaId($this->em->find('AppBundle:Organization', $aOwner[1]));
                break;
            default:
                // Error above. Use current user as default.
                // TODO{s:0} Throw error?
                $user = $this->tokenStorage->getToken()->getUser();
                $assoc->setUserId($user);
                break;
        }

        $this->em->persist($assoc);
        $this->em->flush();
        return $assoc;
    }
}
