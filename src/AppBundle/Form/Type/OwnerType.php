<?php
namespace AppBundle\Form\Type;

use AppBundle\Entity\Organization;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class OwnerType extends AbstractType
{
    /** @var EntityManager|null $em */
    protected $em = null;


    protected $tokenStorage = null;

    public function __construct(EntityManager $em, TokenStorage $tokenStorage)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        // TODO{s:0} Unterscheiden ob Admin oder normaler Nutzer

        // normal user
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $choices = ['me' => 'user:' . $user->getId()];

        /** @var Organization $orga */
        foreach ($user->getOrganizations() as $orga) {
            $choices['Organisation: ' . $orga->getName()] = 'orga:'.$orga->getId();
        }

        $resolver->setDefaults(array(
            'choices' => $choices
        ));
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
