<?php

namespace MonIndemnisationJustice\Routing;

use MonIndemnisationJustice\Service\RegistreFonctionnalites;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

#[AutoconfigureTag('routing.expression_language_provider')]
class AppExpressionProvider implements ExpressionFunctionProviderInterface
{
    public function __construct(protected readonly RegistreFonctionnalites $verificateurFonctionnaliteActive)
    {

    }

    public function getFunctions(): array
    {
        return [

            new ExpressionFunction(
                'est_fonctionnalite_active',
                // compilation : doit produire du *code PHP* (exécuté par le matcher compilé), et non une expression.
                // On passe par le localisateur des services `#[AsRoutingConditionService]`, exposé par Symfony via la
                // fonction `service` dans le paramètre `_functions` du contexte de routage.
                static fn (string $feature): string => \sprintf(
                    "(\$context->getParameter('_functions')->get('service')(%s)->estActive(%s))",
                    var_export(RegistreFonctionnalites::class, true),
                    $feature
                ),
                // évaluation directe : même logique
                fn (array $values, string $feature): bool => $this->verificateurFonctionnaliteActive->estActive($feature)
            ),
        ];
    }
}
