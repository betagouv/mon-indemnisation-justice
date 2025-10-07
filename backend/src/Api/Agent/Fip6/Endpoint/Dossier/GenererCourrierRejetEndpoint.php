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

#[Route('/api/agent/fip6/dossier/{id}/generer-courrier-rejet', name: 'api_agent_fip6_dossier_generer_courrier_rejet', methods: ['POST'])]
#[IsGranted(DossierVoter::ACTION_GENERER_DOCUMENT, 'dossier', message: "Seul l'agent rédacteur attribué ou un agent validateur peut générer les courriers d'un dossier", statusCode: Response::HTTP_FORBIDDEN)]
class GenererCourrierRejetEndpoint
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
        GenererCourrierRejetInput $input,
    ) {
        $courrierMinistere = $dossier->getOrCreatePropositionIndemnisation();

        $courrierMinistere->setMetaDonnees(['indemnisation' => false,
            'motifRejet' => $input->motifRejet,
        ]);

        $gabarit = match ($input->motifRejet) {
            'est_bailleur' => 'courrier/_corps_rejete_bailleur.html.twig',
            'est_vise' => 'courrier/_corps_rejete_est_vise.html.twig',
            'est_hebergeant' => 'courrier/_corps_rejete_est_hebergeant.html.twig',
            default => 'courrier/_corps_rejete.html.twig'
        };

        $courrierMinistere->setCorps(
            $this->twig->render($gabarit, [
                'dossier' => $dossier,
            ])
        );

        $courrierMinistere = $this->imprimanteCourrier->imprimerDocument($courrierMinistere);

        $this->em->persist($courrierMinistere);
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
