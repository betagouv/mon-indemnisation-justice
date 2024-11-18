<?php

namespace App\Forms;

use App\Dto\TestEligibilite;
use App\Entity\GeoDepartement;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
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
        $tranformer = new CallbackTransformer(
            function ($property) {
                if (null === $property) {
                    return null;
                }
                return $property ? 'true' : 'false';
            },
            function ($property) {
                return $property ? filter_var($property, FILTER_VALIDATE_BOOL) : null;
            }
        );

        $builder
            ->add('departement', EntityType::class, ['class' => GeoDepartement::class])
            ->add('estVise', TextType::class, ['required' => false])
            ->add('estHebergeant', TextType::class)
            ->add('estProprietaire', TextType::class)
            ->add('aContacteAssurance', TextType::class)
            ->add('aContacteBailleur', TextType::class)
        ;
        $builder->get('estVise')->addModelTransformer($tranformer);
        $builder->get('estHebergeant')->addModelTransformer($tranformer);
        $builder->get('estProprietaire')->addModelTransformer($tranformer);
        $builder->get('aContacteAssurance')->addModelTransformer($tranformer);
        $builder->get('aContacteBailleur')->addModelTransformer($tranformer);

    }
}
