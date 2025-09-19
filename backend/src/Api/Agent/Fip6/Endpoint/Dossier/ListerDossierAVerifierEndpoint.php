<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\Fip6\Output\DossierAVerifierOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DossierVoter;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrisPorte;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui retourne à un agent rédacteur la liste de ses dossiers dont la déclaration d'acceptation est à vérifier.
 */
#[Route('/api/agent/fip6/dossiers/liste/a-verifier', name: 'api_agent_dossiers_liste_a_vérifier', methods: ['GET'])]
#[IsGranted(DossierVoter::ACTION_LISTER_A_VERIFIER)]
class ListerDossierAVerifierEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly NormalizerInterface $normalizer,
    ) {}

    public function __invoke(Security $security): Response
    {
        /** @var Agent $agent */
        $agent = $security->getUser();

        return new JsonResponse(
            $this->normalizer->normalize(
                array_map(
                    /* Pas réussi à utiliser l'ObjectMapper ici : il se plaint de ne pas trouver les champs
                    `dateValidation` et `agentValidateur` dans la classe source, ce qui est tout de même ballot pour un
                    mapper ... Et je n'ai pas non plus réussi à utiliser des _arrow function_ en guise de callable
                    transformer, pas plus que de déléguer à un transformer de classe (jamais appelé ...).
                    */
                    fn (BrisPorte $dossier) => DossierAVerifierOutput::creerDepuisDossier($dossier),
                    array_values($agent->getDossiersAVerifier())
                ),
                'json',
            )
        );
    }
}
