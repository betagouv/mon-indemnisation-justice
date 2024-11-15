<?php

namespace App\Forms;

use App\Dto\Inscription;
use App\Entity\Civilite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InscriptionType extends AbstractType
{
    public function getBlockPrefix()
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Inscription::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => self::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('civilite', EnumType::class, [
                'class' => Civilite::class,
            ])
            ->add('prenom', TextType::class)
            ->add('nom', TextType::class)
            ->add('nomNaissance', TextType::class)
            ->add('courriel', TextType::class)
            ->add('telephone', TextType::class)
            ->add('motDePasse', TextType::class)
            ->add('confirmation', TextType::class)
            ->add('cguOk', CheckboxType::class)
        ;
    }
}
