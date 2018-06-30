<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Form;

use AppBundle\Form\Type\OwnerType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Validator\Constraints\NotBlank;

class ApiKeyForm extends AbstractType
{
    /** @var EntityManager|null $em */
    protected $em = null;

    /** @var TokenStorage|null */
    protected $tokenStorage = null;

    public function __construct(EntityManager $em, TokenStorage $tokenStorage)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Titel',
                'constraints' => [
                    new NotBlank(),
                    // TODO{s:1} constraint: unique for user/orga
                ],
            ])
            ->add('code', TextType::class, ['disabled' => true, 'data' => '.....'])
            ->add('owner_string', OwnerType::class, ['label' => 'Owner', 'ownable' => $builder->getData()])
            ->add('create', SubmitType::class);
    }
}
