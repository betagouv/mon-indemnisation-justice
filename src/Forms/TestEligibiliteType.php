<?php

namespace App\Forms;

use App\Dto\TestEligibilite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateType;
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
            'allow_extra_fields' => true
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $tranformer = new CallbackTransformer(
            function ($property) {
                return $property ? 'true' : 'false';
            },
            function ($property) {
                return filter_var($property, FILTER_VALIDATE_BOOL);
            }
        );

        $builder
            ->add('dateOperationPJ', DateType::class, ['widget' => 'single_text', 'format' => 'yyyy-MM-dd'])
            ->add('estVise', TextType::class, ['required' => false])
            ->add('estRecherche', TextType::class)
            ->add('estProprietaire', TextType::class)
            ->add('aContacteAssurance', TextType::class)
            ->add('aContacteBailleur', TextType::class)
        ;
        $builder->get('estVise')->addModelTransformer($tranformer);
        $builder->get('estRecherche')->addModelTransformer($tranformer);
        $builder->get('estProprietaire')->addModelTransformer($tranformer);
        $builder->get('aContacteAssurance')->addModelTransformer($tranformer);
        $builder->get('aContacteBailleur')->addModelTransformer($tranformer);

    }
}
