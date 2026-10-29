<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\FDO\Input\DeclarationFDOBrisPorteInput;
use MonIndemnisationJustice\Api\Agent\FDO\Output\DeclarationFDOBrisPorteOutput;
use MonIndemnisationJustice\Api\Agent\FDO\Voter\DeclarationFDOBrisPorteVoter;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui permet à un agent des FDO d'éditer une déclaration d'erreur opérationnelle.
 *
 * Seules les données fournies en entrée sont reportées sur la déclaration, celles manquantes sont ignorées.
 */
#[Route('/api/agent/fdo/bris-de-porte/{declarationId}/editer', name: 'api_agent_fdo_bris_porte_editer', methods: ['PATCH'])]
#[IsGranted(
    DeclarationFDOBrisPorteVoter::ACTION_EDITER,
    'declaration',
    message: "La déclaration d'une erreur opérationnelle est retreinte aux agents des Forces de l'Ordre",
    statusCode: Response::HTTP_FORBIDDEN
)]
class EditerDeclarationBrisPorteEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly NormalizerInterface $normalizer,
        protected readonly DenormalizerInterface $denormalizer,
    ) {
    }

    public function __invoke(
        #[MapEntity(id: 'declarationId', message: 'Déclaration inconnue')]
        DeclarationFDOBrisPorte $declaration,
        Request $request,
    ): Response {
        $input = DeclarationFDOBrisPorteInput::depuisDeclaration($declaration);
        $charge = json_decode($request->getContent(), true);

        if (is_array($charge) && !empty($charge)) {
            try {
                // On enrichit l'input, construit depuis la déclaration, avec les valeurs fournies
                $input = $this->denormalizer->denormalize(
                    $charge,
                    DeclarationFDOBrisPorteInput::class,
                    context: [
                        AbstractNormalizer::OBJECT_TO_POPULATE => $input,
                        AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true,
                        AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,
                    ]
                );
            } catch (UnexpectedValueException $e) {
                return new JsonResponse([
                    'erreur' => $e->getMessage(),
                ], Response::HTTP_BAD_REQUEST);
            } catch (ExtraAttributesException $e) {
                return new JsonResponse([
                    'erreur' => (count($e->getExtraAttributes()) > 1 ? 'Champs non reconnus' : 'Champ non reconnu').': '.implode(', ', $e->getExtraAttributes()),
                ], Response::HTTP_BAD_REQUEST);
            }

            $input->versDeclaration($declaration);

            $this->em->persist($declaration);
            $this->em->flush();
        }

        return new JsonResponse(
            $this->normalizer->normalize(
                DeclarationFDOBrisPorteOutput::depuisDeclarationFDO($declaration),
                'json'
            ),
            Response::HTTP_CREATED
        );
    }
}
