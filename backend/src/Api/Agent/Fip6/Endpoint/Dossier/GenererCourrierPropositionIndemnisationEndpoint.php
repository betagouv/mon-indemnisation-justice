<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\Fip6\Output\DocumentOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DossierVoter;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Service\ImprimanteCourrier;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Twig\Environment;

#[Route('/api/agent/fip6/dossier/{id}/generer-courrier-proposition-indemnisation', name: 'api_agent_fip6_dossier_generer_courrier_proposition_indemnisation', methods: ['POST'])]
#[IsGranted(DossierVoter::ACTION_GENERER_DOCUMENT, 'dossier', message: "Seul l'agent rédacteur attribué ou un agent validateur peut générer les courriers d'un dossier", statusCode: Response::HTTP_FORBIDDEN)]
class GenererCourrierPropositionIndemnisationEndpoint
{
    public function __construct(
        protected readonly ImprimanteCourrier $imprimanteCourrier,
        protected readonly Environment $twig,
        protected readonly EntityManagerInterface $em,
        protected readonly NormalizerInterface $normalizer,
        protected readonly ObjectMapperInterface $objectMapper,
    ) {}

    public function __invoke(
        #[MapEntity]
        BrisPorte $dossier,
        #[MapRequestPayload]
        GenererCourrierPropositionIndemnisationInput $input,
    ) {
        $courrierPropositionIndemnisation = $dossier->getOrCreatePropositionIndemnisation();

        $courrierPropositionIndemnisation->setMetaDonnees([
            'indemnisation' => true,
            'montantIndemnisation' => $input->montantIndemnisation,
        ]);

        $gabarit = $dossier->getRequerant()->getIsPersonneMorale() ? 'courrier/_corps_accepte_personne_morale.html.twig' : 'courrier/_corps_accepte_personne_physique.html.twig';

        $courrierPropositionIndemnisation->setCorps(
            $this->twig->render($gabarit, [
                'dossier' => $dossier,
                'montantIndemnisation' => $input->montantIndemnisation,
            ])
        );

        $courrierPropositionIndemnisation = $this->imprimanteCourrier->imprimerDocument($courrierPropositionIndemnisation);

        $this->em->persist($courrierPropositionIndemnisation);
        $this->em->persist($dossier);
        $this->em->flush();

        return new JsonResponse(
            ['document' => $this->normalizer->normalize(
                $this->objectMapper->map($dossier->getCourrierDecision(), DocumentOutput::class),
                'json'
            )],
            Response::HTTP_CREATED
        );
    }
}
