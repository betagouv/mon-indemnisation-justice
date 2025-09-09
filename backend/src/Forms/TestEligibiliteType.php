<?php

namespace MonIndemnisationJustice\Forms;

use MonIndemnisationJustice\Entity\QualiteRequerant;
use MonIndemnisationJustice\Entity\TestEligibilite;
use MonIndemnisationJustice\Forms\Type\LiteralBooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TestEligibiliteType extends AbstractType
{
    public function getBlockPrefix(): string
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
            ->add('estIssuAttestation', LiteralBooleanType::class)
            ->add('estVise', LiteralBooleanType::class)
            ->add('estHebergeant', LiteralBooleanType::class)
            ->add(
                'rapportAuLogement',
                EnumType::class,
                [
                    'class' => QualiteRequerant::class,
                    'required' => true,
                ]
            )
            ->add('aContacteAssurance', LiteralBooleanType::class)
            ->add('aContacteBailleur', LiteralBooleanType::class)
        ;
    }
}
