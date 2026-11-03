<?php

namespace MonIndemnisationJustice\Routing;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

#[AutoconfigureTag('routing.expression_language_provider')]
class AppExpressionProvider implements ExpressionFunctionProviderInterface
{
    public function __construct(protected readonly VerificateurFonctionnaliteActive $verificateurFonctionnaliteActive)
    {

    }

    public function getFunctions(): array
    {
        return [

            new ExpressionFunction(
                'est_fonctionnalite_active',
                static fn (string $feature): string => \sprintf(
                    "service('MonIndemnisationJustice\\Routing\\VerificateurFonctionnaliteActive').estActive(%s)",
                    $feature
                ),
                // évaluation directe : même logique
                fn (array $values, string $feature): bool => $this->verificateurFonctionnaliteActive->estActive($feature)
            ),
        ];
    }
}
