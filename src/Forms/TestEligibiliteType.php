<?php

namespace App\Forms;

use App\Dto\Inscription;
use App\Dto\TestEligibilite;
use App\Entity\Civilite;
use Doctrine\DBAL\Types\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TestEligibiliteType extends AbstractType
{
    public function getBlockPrefix()
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TestEligibilite::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => self::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateOperationPJ', DateTimeType::class)
            ->add('estVise', BooleanType::class)
            ->add('estRecherche', BooleanType::class)
            ->add('estProprietaire', BooleanType::class)
            ->add('aContacteAssurance', BooleanType::class)
            ->add('aContacteBailleur', BooleanType::class)
        ;
    }
}
