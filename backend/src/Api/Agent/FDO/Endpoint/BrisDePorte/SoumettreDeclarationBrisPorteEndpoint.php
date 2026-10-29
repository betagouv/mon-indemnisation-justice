<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\FDO\Input\DeclarationFDOBrisPorteInput;
use MonIndemnisationJustice\Api\Agent\FDO\Output\DeclarationFDOBrisPorteOutput;
use MonIndemnisationJustice\Api\Agent\FDO\Voter\DeclarationFDOBrisPorteVoter;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Route API qui permet à un agent des FDO de soumettre le brouillon d'une déclaration d'erreur opérationnelle.
 */
#[Route('/api/agent/fdo/bris-de-porte/{declarationId}/soumettre', name: 'api_agent_fdo_bris_porte_soumettre', methods: ['POST'])]
#[IsGranted(
    DeclarationFDOBrisPorteVoter::ACTION_SOUMETTRE,
    'declaration',
    message: "La déclaration d'une erreur opérationnelle est retreinte aux agents des Forces de l'Ordre",
    statusCode: Response::HTTP_FORBIDDEN
)]
class SoumettreDeclarationBrisPorteEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly NormalizerInterface $normalizer,
        protected readonly ValidatorInterface $validator,
    ) {
    }

    public function __invoke(
        #[MapEntity(id: 'declarationId', message: 'Déclaration inconnue')]
        DeclarationFDOBrisPorte $declaration,
    ): Response {
        /** @var ConstraintViolationList $validation */
        $violations = $this->validator->validate(DeclarationFDOBrisPorteInput::depuisDeclaration($declaration));

        if ($violations->count() > 0) {
            return new JsonResponse([
                'erreurs' => array_merge(
                    ...array_map(
                        fn ($v) => [$v->getPropertyPath() => $v->getMessage()],
                        iterator_to_array($violations->getIterator())
                    )
                ),
            ], Response::HTTP_BAD_REQUEST);
        }

        // La soumission déclenche l'envoi du mail d'invitation à déposer (cf. DeclarationBrisPorteFDOEntitylistener)
        $declaration->setDateSoumission(new \DateTimeImmutable());

        $this->em->persist($declaration);
        $this->em->flush();

        return new JsonResponse(
            $this->normalizer->normalize(
                DeclarationFDOBrisPorteOutput::depuisDeclarationFDO($declaration),
                'json'
            ),
            Response::HTTP_CREATED
        );
    }
}
