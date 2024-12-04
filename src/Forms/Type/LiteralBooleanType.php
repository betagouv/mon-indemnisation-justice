<?php

namespace App\Forms\Type;

use App\Forms\Transformer\LiteralBooleanTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Permet de manipuler des booléens exprimé en _plain javascript_.
 */
class LiteralBooleanType extends AbstractType
{
    public function __construct(
        protected readonly LiteralBooleanTransformer $transformer,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->transformer);
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
