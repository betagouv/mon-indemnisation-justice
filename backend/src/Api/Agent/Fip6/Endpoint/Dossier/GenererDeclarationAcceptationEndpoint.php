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

#[Route('/api/agent/fip6/dossier/{id}/generer-declaration-acceptation', name: 'api_agent_fip6_dossier_generer_declaration_acceptation', methods: ['POST'])]
#[IsGranted(DossierVoter::ACTION_GENERER_DOCUMENT, 'dossier', message: "Seul l'agent rédacteur attribué ou un agent validateur peut générer la déclaration d'acceptation", statusCode: Response::HTTP_FORBIDDEN)]
class GenererDeclarationAcceptationEndpoint
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
        $declarationAcceptation = $dossier->getOrCreateDeclarationAcceptation();

        $declarationAcceptation->setMetaDonnees([
            'montantIndemnisation' => $input->montantIndemnisation,
        ]);

        $declarationAcceptation->setCorps(
            $this->twig->render('courrier/_corps_declaration_acceptation.html.twig', [
                'dossier' => $dossier,
                'contexte' => $declarationAcceptation->getMetaDonnees() ?? [],
            ])
        );

        $declarationAcceptation = $this->imprimanteCourrier->imprimerDocument($declarationAcceptation);

        $this->em->persist($declarationAcceptation);
        $this->em->persist($dossier);
        $this->em->flush();

        return new JsonResponse(
            ['document' => $this->normalizer->normalize(
                $this->objectMapper->map($declarationAcceptation, DocumentOutput::class),
                'json'
            )],
            Response::HTTP_CREATED
        );
    }
}
