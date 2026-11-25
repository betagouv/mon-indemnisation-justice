<?php

namespace MonIndemnisationJustice\Service\Signature;

use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\DossierDto;
use MonIndemnisationJustice\Entity\RequeteSignatureRequerant;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Diffuse, via Mercure, au requérant abonné à sa requête de signature électronique les changements la concernant.
 */
class DiffuseurSignatureRequerant
{
    public function __construct(
        protected readonly HubInterface $hub,
        protected readonly NormalizerInterface $normalizer,
        protected readonly Authorization $authorization,
        protected readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Publie le dossier, à jour, sur le topic de la requête de signature. La mise à jour est privée : seul un abonné
     * muni d'un JWT autorisant ce topic (cf. `Authorization::setCookie`) la recevra.
     */
    public function diffuserSignature(RequeteSignatureRequerant $requeteSignature): void
    {
        $this->hub->publish(new Update(
            "requerant/signature/{$requeteSignature->getId()}",
            json_encode(
                $this->normalizer->normalize(DossierDto::depuisDossier($requeteSignature->getDossier()), 'json'),
                JSON_THROW_ON_ERROR
            ),
            private: true,
        ));
    }

    /**
     * Autorise le navigateur à s'abonner au topic de la requête de signature, tant que celle-ci n'est pas signée : le
     * cookie `mercureAuthorization` (JWT restreint à ce seul topic) est ajouté à la réponse de la requête courante.
     */
    public function autoriserAbonnement(Request $request, ?RequeteSignatureRequerant $requeteSignature): void
    {
        if (null === $requeteSignature || $requeteSignature->estSignee()) {
            return;
        }

        try {
            $this->authorization->setCookie($request, ["requerant/signature/{$requeteSignature->getId()}"]);
        } catch (\Throwable $exception) {
            // Ex : le domaine du hub ne correspond pas à celui de la requête. La page fonctionne toujours, sans mise à jour en direct
            $this->logger->warning("Impossible d'autoriser l'abonnement Mercure à la requête de signature {$requeteSignature->getId()}", [
                'erreur' => $exception->getMessage(),
            ]);
        }
    }
}
