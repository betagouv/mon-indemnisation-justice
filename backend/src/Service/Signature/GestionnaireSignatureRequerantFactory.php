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
 * Par ordre de priorité, selon ceux qui sont configurés : Documenso sinon rien.
 */
class GestionnaireSignatureRequerantFactory
{
    /**
     * @param \Closure(): GestionnaireSignatureRequerantDocumenso $gestionnaireDocumenso
     */
    public function __construct(
        #[AutowireServiceClosure(GestionnaireSignatureRequerantDocumenso::class)]
        protected readonly \Closure $gestionnaireDocumenso,
        #[Autowire(env: 'default::DOCUMENSO_BASE_API_URI')]
        protected readonly ?string $documensoBaseApiUri = null,
    ) {
    }

    public function creerGestionnaireSignatureRequerant(): ?GestionnaireSignatureRequerantInterface
    {
        if (!empty($this->documensoBaseApiUri)) {
            return ($this->gestionnaireDocumenso)();
        }

        return null;
    }
}
