<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 28.12.16
 * Time: 19:37
 */


namespace AppBundle\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * AppBundle\Entity\User
 *
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User extends BaseUser {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="Organization", inversedBy="users")
     * @ORM\JoinTable(name="users_organizations")
     */
    private $organizations;


    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    public function getAllowedPoolPaths()
    {
        $r = array();
        $r[] = 'user-' . $this->id;

        $orgas = $this->getOrganizations();
        foreach ($orgas as $orga) { /** @var Organization $orga */
            $r[] = 'orga-' . $orga->getId();
        }

        return $r;
    }

    public function isPoolFileAllowed($path)
    {
        $file = ltrim($path, "/\r\n\t ");
        $base = substr($file, 0, strpos($file, '/'));
        return (in_array($base, $this->getAllowedPoolPaths()));
    }

    public function isPresentationAllowed(Presentation $presentation)
    {
        if ($presentation->getOwnerUser() == $this) return true;

        $orgas = $this->getOrganizations();
        foreach ($orgas as $orga) { /** @var Organization $orga */
            if ($presentation->getOwnerOrga() == $orga) return true;
        }

        return false;
    }

    public function isSlideAllowed(Slide $slide)
    {
        return $this->isPresentationAllowed($slide->getPresentation());
    }

    /**
     * Add organization
     *
     * @param \AppBundle\Entity\Organization $organization
     *
     * @return User
     */


    public function getPresentations(EntityManager $em) {
        $user = $this;
        $rep = $em->getRepository('AppBundle:Presentation');
        $pres = array();

        $pres_user = $rep->findBy(array('owner_user' => $user));

        foreach ($pres_user as $p) {
            $pres['me'][] = $p;
        }

        $orgas = $user->getOrganizations();
        foreach ($orgas as $orga) { /** @var Organization $orga */
            $pres_orga = $rep->findBy(array('owner_orga' => $orga));
            foreach ($pres_orga as $p) {
                $pres[$orga->getName()][] = $p;
            }
        }

        return $pres;
    }

    public function addOrganization(\AppBundle\Entity\Organization $organization)
    {
        $this->organizations[] = $organization;

        return $this;
    }

    /**
     * Remove organization
     *
     * @param \AppBundle\Entity\Organization $organization
     */
    public function removeOrganization(\AppBundle\Entity\Organization $organization)
    {
        $this->organizations->removeElement($organization);
    }

    /**
     * Get organizations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrganizations()
    {
        return $this->organizations;
    }
}
