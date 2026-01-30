<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\FDO\Input\DeclarationFDOBrisPorteInput;
use MonIndemnisationJustice\Api\Agent\FDO\Voter\DeclarationFDOBrisPorteVoter;
use MonIndemnisationJustice\Entity\BrouillonDeclarationFDOBrisPorte;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Route API qui permet à un agent des FDO de déclarer une erreur opérationnelle.
 *
 * @internal
 *
 * @coversNothing
 */
#[Route('/api/agent/fdo/bris-de-porte/{declarationId}/editer', name: 'api_agent_fdo_bris_porte_editer', methods: ['PATCH'])]
#[IsGranted(
    DeclarationFDOBrisPorteVoter::ACTION_EDITER,
    'brouillon',
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
        protected readonly ValidatorInterface $validator,
    ) {}

    public function __invoke(
        #[MapEntity(id: 'declarationId', message: 'Déclaration inconnue')]
        BrouillonDeclarationFDOBrisPorte $brouillon,
        Request $request,
        Security $security
    ): Response {
        $brouillon->ajouterDonnees(
            json_decode($request->getContent(), true)
        );

        try {
            $input = $this->denormalizer->denormalize($brouillon->getDonnees(), DeclarationFDOBrisPorteInput::class, context: [AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false]);
        } catch (UnexpectedValueException $e) {
            return new JsonResponse([
                'erreur' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (ExtraAttributesException $e) {
            return new JsonResponse([
                'erreur' => (count($e->getExtraAttributes()) > 1 ? 'Champs non reconnus' : 'Champ non reconnu').': '.implode(', ', $e->getExtraAttributes()),
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($brouillon);
        $this->em->flush();

        return new JsonResponse(
            $this->normalizer->normalize(
                $input,
                'json',
                // [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]
            ),
            Response::HTTP_CREATED
        );
    }
}
