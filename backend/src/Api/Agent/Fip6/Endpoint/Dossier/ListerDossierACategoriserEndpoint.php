<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\Fip6\Output\DossierACategoriserOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DossierVoter;
use MonIndemnisationJustice\Entity\BrisPorte;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui retourne à un agent Beta.gouv la liste des dossiers à catégoriser selon les données figurant sur les
 * pièces jointes d'attestation.
 */
#[Route('/api/agent/fip6/dossiers/liste/a-categoriser', name: 'api_agent_dossiers_liste_a_categoriser', methods: ['GET'])]
#[IsGranted(DossierVoter::ACTION_LISTER_A_ATTRIBUER)]
class ListerDossierACategoriserEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly NormalizerInterface $normalizer,
    ) {}

    public function __invoke(): Response
    {
        $dossiers = $this->entityManager->getRepository(BrisPorte::class)->getDossierACategoriser();

        return new JsonResponse(
            $this->normalizer->normalize(
                array_map(
                    /* Pas réussi à utiliser l'ObjectMapper ici : il se plaint de ne pas trouver les champs
                    `dateValidation` et `agentValidateur` dans la classe source, ce qui est tout de même ballot pour un
                    mapper ... Et je n'ai pas non plus réussi à utiliser des _arrow function_ en guise de callable
                    transformer, pas plus que de déléguer à un transformer de classe (jamais appelé ...).
                    */
                    fn (BrisPorte $dossier) => DossierACategoriserOutput::creerDepuisDossier($dossier),
                    $dossiers
                ),
                'json'
            )
        );
    }
}
