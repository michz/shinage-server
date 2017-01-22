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
use Rollerworks\Bundle\PasswordStrengthBundle\Validator\Constraints as RollerworksPassword;

/**
 * AppBundle\Entity\User
 *
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User extends BaseUser
{
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
    protected $organizations;


    /**
     * @RollerworksPassword\PasswordRequirements(requireLetters=true, requireNumbers=true)
     */
    protected $password;

    /**
     * @RollerworksPassword\PasswordRequirements(requireLetters=true, requireNumbers=true)
     */
    protected $plainPassword;


    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    public function setEmail($email)
    {
        parent::setUsername($email);
        parent::setUsernameCanonical($email);
        return parent::setEmail($email);
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
        if ($presentation->getOwnerUser() == $this) {
            return true;
        }

        $orgas = $this->getOrganizations();
        foreach ($orgas as $orga) { /** @var Organization $orga */
            if ($presentation->getOwnerOrga() == $orga) {
                return true;
            }
        }

        return false;
    }

    public function isSlideAllowed(Slide $slide)
    {
        return $this->isPresentationAllowed($slide->getPresentation());
    }


    public function getPresentations(EntityManager $em)
    {
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

    /**
     * Add organization
     *
     * @param \AppBundle\Entity\Organization $organization
     *
     * @return User
     */
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


    public static function generateToken()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
