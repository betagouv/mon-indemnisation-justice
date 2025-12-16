<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\FDO\Input\DeclarationFDOBrisPorteInput;
use MonIndemnisationJustice\Api\Agent\FDO\Transformers\DeclarationFDOBrisPorteOutputMapper;
use MonIndemnisationJustice\Api\Agent\FDO\Voter\DeclarationFDOBrisPorteVoter;
use MonIndemnisationJustice\Entity\BrouillonDeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Service\Mailer;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui permet à un agent des FDO de déclarer une erreur opérationnelle.
 */
#[Route('/api/agent/fdo/bris-de-porte/{id}/editer', name: 'api_agent_fdo_bris_porte_editer', methods: ['PATCH'])]
#[IsGranted(
    DeclarationFDOBrisPorteVoter::ACTION_DECLARER,
    message: "La déclaration d'une erreur opérationnelle est retreinte aux agents des Forces de l'Ordre",
    statusCode: Response::HTTP_FORBIDDEN
)]
class EditerDeclarationBrisPorteEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ObjectMapperInterface $objectMapper,
        protected readonly NormalizerInterface $normalizer,
        protected readonly DenormalizerInterface $denormalizer,
        protected readonly Mailer $mailer,
    ) {}

    public function __invoke(#[MapEntity(id: 'id')] BrouillonDeclarationFDOBrisPorte $brouillon, Request $request, Security $security): Response
    {
        $brouillon->ajouterDonnees(json_decode($request->getContent(), true));
        $this->em->persist($brouillon);
        $this->em->flush();

        $input = $this->denormalizer->denormalize($brouillon->getDonnees(), DeclarationFDOBrisPorteInput::class);
        $declaration = $this->objectMapper->map($input, DeclarationFDOBrisPorte::class);

        return new JsonResponse([
            $this->normalizer->normalize(
                DeclarationFDOBrisPorteOutputMapper::mapper($declaration, $this->objectMapper),
                'json'
            ),
        ], Response::HTTP_CREATED);
    }
}
