<?php

namespace MonIndemnisationJustice\Forms;

use MonIndemnisationJustice\Dto\ModificationMotDePasse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModificationMotDePasseType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return '';
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ModificationMotDePasse::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => self::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ...
            ->add('motDePasse', TextType::class)
            ->add('confirmation', TextType::class)
        ;
    }
}
