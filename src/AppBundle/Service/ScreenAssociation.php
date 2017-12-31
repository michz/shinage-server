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
            $assoc = $rep->findBy(array('user_id' => $o->getId()));
            foreach ($assoc as $a) {
                $r[] = $a->getScreen();
            }
        }

        return $r;
    }

    public function isUserAllowed(Screen $screen, User $user)
    {
        return $this->isUserAllowedTo($screen, $user, 'author');
    }


    public function isUserAllowedTo(Screen $screen, User $user, string $attribute)
    {
        $rep = $this->em->getRepository('AppBundle:ScreenAssociation');
        $assoc = $rep->findBy(array('screen' => $screen->getGuid()));
        $orgas = $user->getOrganizations();

        foreach ($assoc as $a) { /** @var \AppBundle\Entity\ScreenAssociation $a */
            if ($a->getUserId() == $user) {
                return $this->roleGreaterOrEqual($a->getRole(), $attribute);
            }

            foreach ($orgas as $o) { /** @var User $o */
                if ($a->getUserId() == $o) {
                    return $this->roleGreaterOrEqual($a->getRole(), $attribute);
                }
            }
        }

        return false;
    }

    private function roleGreaterOrEqual($granted, $reference)
    {
        dump($granted);
        dump($reference);
        if ($granted === 'admin' || $granted === $reference) {
            return true;
        }
        if ($granted === 'manage' && $reference === 'author') {
            return true;
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
            case 'orga':
                $assoc->setUserId($this->em->find('AppBundle:User', $aOwner[1]));
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
