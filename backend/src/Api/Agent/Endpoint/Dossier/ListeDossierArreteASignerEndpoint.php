<?php

namespace MonIndemnisationJustice\Api\Agent\Endpoint\Dossier;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\Resources\Output\DossierArreteASignerOutput;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\EtatDossierType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui retourne à un agent validateur la liste des dossiers dont l'arrêté de paiement est à signer.
 */
#[Route('/api/agent/dossiers/liste/arrete-a-signer', name: 'api_agent_dossiers_liste_arrete_a_signer', methods: ['GET'])]
#[IsGranted('lister:dossiers:arrete-a-signer')]
class ListeDossierArreteASignerEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly NormalizerInterface $normalizer,
    ) {}

    public function __invoke(): Response
    {
        $dossiers = $this->entityManager->getRepository(BrisPorte::class)->listerDossierParEtat(EtatDossierType::DOSSIER_OK_VERIFIE);

        return new JsonResponse(
            $this->normalizer->normalize(
                array_map(
                    /* Pas réussi à utiliser l'ObjectMapper ici : il se plaitn de ne pas trouver les champs
                    `dateValidation` et `agentValidateur` dans la classe source, ce qui est tout de même ballot pour un
                    mapper ... Et je n'ai pas non plus réussi à utiliser des _arrow function_ en guise de callable
                    transformer, pas plus que de déléguer à un transformer de classe (jamais appelé ...).
                    */
                    fn (BrisPorte $dossier) => DossierArreteASignerOutput::creerDepuisDossier($dossier),
                    $dossiers
                ),
                'json'
            )
        );
    }
}
