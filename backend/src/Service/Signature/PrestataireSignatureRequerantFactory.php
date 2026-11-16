<?php

namespace MonIndemnisationJustice\Service\Signature;

use MonIndemnisationJustice\Service\Signature\Prestataire\Requerant\Documenso\PrestataireSignatureRequerantDocumenso;
use MonIndemnisationJustice\Service\Signature\Prestataire\Requerant\LegalySpace\PrestataireSignatureLegalySpace;
use MonIndemnisationJustice\Service\Signature\Prestataire\Requerant\PrestataireSignatureRequerantInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireServiceClosure;

/**
 * Service autowiré (via config/services.yaml) dont la méthode `creerGestionnaireSignatureRequerant` sert de factory
 * pour {@see PrestataireSignatureRequerantInterface} (voir config/packages/signature.yaml).
 *
 * Les implémentations sont injectées sous forme de closures : elles ne sont instanciées (avec leurs propres
 * dépendances autowirées) que si elles sont effectivement retenues.
 *
 * Par ordre de priorité, selon ceux qui sont configurés : LegalySpace, sinon Documenso, sinon rien.
 */
class PrestataireSignatureRequerantFactory
{
    /**
     * @param \Closure(): PrestataireSignatureLegalySpace        $gestionnaireLegalySpace
     * @param \Closure(): PrestataireSignatureRequerantDocumenso $gestionnaireDocumenso
     */
    public function __construct(
        #[AutowireServiceClosure(PrestataireSignatureLegalySpace::class)]
        protected readonly \Closure $gestionnaireLegalySpace,
        #[AutowireServiceClosure(PrestataireSignatureRequerantDocumenso::class)]
        protected readonly \Closure $gestionnaireDocumenso,
        #[Autowire(env: 'default::DOCUMENSO_BASE_API_URI')]
        protected readonly ?string $documensoBaseApiUri = null,
        #[Autowire(env: 'default::LEGALYSPACE_API_KEY')]
        protected readonly ?string $legalySpaceApiKey = null,
    ) {
    }

    public function creerPrestataireSignatureRequerant(): ?PrestataireSignatureRequerantInterface
    {
        if (!empty($this->legalySpaceApiKey)) {
            return ($this->gestionnaireLegalySpace)();
        }

        if (!empty($this->documensoBaseApiUri)) {
            return ($this->gestionnaireDocumenso)();
        }

        return null;
    }
}
