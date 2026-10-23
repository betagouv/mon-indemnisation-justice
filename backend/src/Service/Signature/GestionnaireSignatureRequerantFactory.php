<?php

namespace MonIndemnisationJustice\Service\Signature;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireServiceClosure;

/**
 * Service autowiré (via config/services.yaml) dont la méthode `creerGestionnaireSignatureRequerant` sert de factory
 * pour {@see GestionnaireSignatureRequerantInterface} (voir config/packages/signature.yaml).
 *
 * Les implémentations sont injectées sous forme de closures : elles ne sont instanciées (avec leurs propres
 * dépendances autowirées) que si elles sont effectivement retenues.
 */
class GestionnaireSignatureRequerantFactory
{
    /**
     * @param \Closure(): GestionnaireSignatureRequerantYoutrust $gestionnaireYoutrust
     */
    public function __construct(
        #[AutowireServiceClosure(GestionnaireSignatureRequerantYoutrust::class)]
        protected readonly \Closure $gestionnaireYoutrust,
        #[Autowire(env: 'default::YOUTRUST_API_KEY')]
        protected readonly ?string $youtrustApiKey = null,
    ) {
    }

    public function creerGestionnaireSignatureRequerant(): ?GestionnaireSignatureRequerantInterface
    {
        if (!empty($this->youtrustApiKey)) {
            return ($this->gestionnaireYoutrust)();
        }

        return null;
    }
}
