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
 *
 * Youtrust est prioritaire sur DocuSeal si les deux sont configurés.
 */
class GestionnaireSignatureRequerantFactory
{
    /**
     * @param \Closure(): GestionnaireSignatureRequerantYoutrust $gestionnaireYoutrust
     * @param \Closure(): GestionnaireSignatureRequerantDocuseal $gestionnaireDocuseal
     */
    public function __construct(
        #[AutowireServiceClosure(GestionnaireSignatureRequerantYoutrust::class)]
        protected readonly \Closure $gestionnaireYoutrust,
        #[AutowireServiceClosure(GestionnaireSignatureRequerantDocuseal::class)]
        protected readonly \Closure $gestionnaireDocuseal,
        #[Autowire(env: 'default::YOUTRUST_API_KEY')]
        protected readonly ?string $youtrustApiKey = null,
        #[Autowire(env: 'default::DOCUSEAL_BASE_API_URI')]
        protected readonly ?string $docusealBaseApiUri = null,
    ) {
    }

    public function creerGestionnaireSignatureRequerant(): ?GestionnaireSignatureRequerantInterface
    {
        if (!empty($this->youtrustApiKey)) {
            return ($this->gestionnaireYoutrust)();
        }

        if (!empty($this->docusealBaseApiUri)) {
            return ($this->gestionnaireDocuseal)();
        }

        return null;
    }
}
