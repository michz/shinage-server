<?php
/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  28.01.17
 * @time     :  18:49
 */

namespace AppBundle\Form;

use AppBundle\Form\Type\OwnerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraints\LengthValidator;
use Symfony\Component\Validator\Constraints\NotBlank;

class CreateVirtualScreenForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // TODO{s:0} length constraint?
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('owner', OwnerType::class, ['label' => 'Owner', 'ownable' => $builder->getData()])
            ->add('create', SubmitType::class)
        ;
    }
}
