<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Form;

use App\Form\Type\OwnerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ApiKeyForm extends AbstractType
{
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
