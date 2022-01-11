<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Form\Type;

use App\Entity\PresentationInterface;
use App\Entity\User;
use App\Security\LoggedInUserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

// @TODO I think this can be refactored and simplified.
class OwnerType extends AbstractType
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var mixed|PresentationInterface */
    protected $entity;

    /** @var LoggedInUserRepositoryInterface */
    private $loggedInUserRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggedInUserRepositoryInterface $loggedInUserRepository
    ) {
        $this->entityManager = $entityManager;
        $this->loggedInUserRepository = $loggedInUserRepository;
    }

    /**
     * @param mixed[]|array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        // save entity that should be owned
        $this->entity = $options['ownable'];

        if (null !== $this->entity) {
            // Event: While building set default value
            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event): void {
                    $event->setData($this->entity->getOwner()->getId());
                }
            );

            // Event: After submission, modify entity
            $builder->addEventListener(
                FormEvents::SUBMIT,
                function (FormEvent $event): void {
                    $entity = $this->entity;
                    $entity->setOwner($this->entityManager->find('App:User', (int) $event->getData()));
                }
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // TODO{s:0} Unterscheiden ob Admin oder normaler Nutzer
        // normal user
        /** @var User $user */
        $user = $this->loggedInUserRepository->getLoggedInUserOrDenyAccess();

        $choices = ['me' => $user->getId()];

        /** @var User $orga */
        foreach ($user->getOrganizations() as $orga) {
            $choices['Organisation: ' . $orga->getName()] = $orga->getId();
        }

        $resolver->setRequired('ownable');

        $resolver->setDefaults([
            'choices'   => $choices,
            'mapped'    => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
