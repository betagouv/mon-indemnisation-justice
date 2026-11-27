<?php

namespace MonIndemnisationJustice\Service;

use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\DossierDto;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\Usager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Diffuse, via Mercure, au requérant abonné les changements de ses dossiers.
 *
 * Les mises à jour sont privées : seul un abonné muni d'un JWT autorisant explicitement le topic du dossier (cf.
 * {@see autoriserAbonnement()}) les reçoit. L'identifiant (entier) du dossier peut donc figurer dans le topic.
 */
class DiffuseurDossierRequerant
{
    public function __construct(
        protected readonly HubInterface $hub,
        protected readonly NormalizerInterface $normalizer,
        protected readonly Authorization $authorization,
        protected readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Topic Mercure du dossier, également construit côté frontend (`requerant/dossier/${dossier.id}`).
     */
    public static function topic(Dossier|int $dossier): string
    {
        $id = $dossier instanceof Dossier ? $dossier->getId() : $dossier;

        return "requerant/dossier/{$id}";
    }

    /**
     * Publie le dossier, à jour, sur son topic.
     */
    public function diffuserDossier(Dossier $dossier): void
    {
        $this->hub->publish(new Update(
            self::topic($dossier),
            json_encode(
                $this->normalizer->normalize(DossierDto::depuisDossier($dossier), 'json'),
                JSON_THROW_ON_ERROR
            ),
            private: true,
        ));
    }

    /**
     * Autorise le navigateur à s'abonner aux topics de **tous** les dossiers de l'usager : le cookie
     * `mercureAuthorization` est ajouté à la réponse de la requête courante.
     *
     * Il n'existe qu'un cookie par hub : restreint au seul dossier consulté, il retirerait l'autorisation d'un dossier
     * ouvert dans un autre onglet (perdue à la prochaine reconnexion de celui-ci).
     */
    public function autoriserAbonnement(Request $request, ?Usager $usager): void
    {
        if (null === $usager) {
            return;
        }

        $topics = $usager->getDossiersBrisDePorte()
            ->map(fn (Dossier $dossier) => self::topic($dossier))
            ->getValues();

        if (empty($topics)) {
            return;
        }

        try {
            $this->authorization->setCookie($request, $topics);
        } catch (\Throwable $exception) {
            // Ex : le domaine du hub ne correspond pas à celui de la requête. La page fonctionne toujours, sans mise à jour en direct
            $this->logger->warning("Impossible d'autoriser l'abonnement Mercure aux dossiers de l'usager {$usager->getId()}", [
                'erreur' => $exception->getMessage(),
            ]);
        }
    }
}
