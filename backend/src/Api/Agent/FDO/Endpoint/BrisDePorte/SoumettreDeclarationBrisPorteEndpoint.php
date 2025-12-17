<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\FDO\Input\DeclarationFDOBrisPorteInput;
use MonIndemnisationJustice\Api\Agent\FDO\Output\DeclarationFDOBrisPorteOutput;
use MonIndemnisationJustice\Api\Agent\FDO\Voter\DeclarationFDOBrisPorteVoter;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrouillonDeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Service\Mailer;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Route API qui permet à un agent des FDO de déclarer une erreur opérationnelle.
 */
#[Route('/api/agent/fdo/bris-de-porte/{id}/soumettre', name: 'api_agent_fdo_bris_porte_soumettre', methods: ['POST'])]
#[IsGranted(
    DeclarationFDOBrisPorteVoter::ACTION_DECLARER,
    message: "La déclaration d'une erreur opérationnelle est retreinte aux agents des Forces de l'Ordre",
    statusCode: Response::HTTP_FORBIDDEN
)]
class SoumettreDeclarationBrisPorteEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ObjectMapperInterface $objectMapper,
        protected readonly NormalizerInterface $normalizer,
        protected readonly DenormalizerInterface $denormalizer,
        protected readonly ValidatorInterface $validator,
        protected readonly Mailer $mailer,
    ) {}

    public function __invoke(Uuid $id, Security $security): Response
    {
        /** @var Agent $agent */
        $agent = $security->getUser();

        /** @var BrouillonDeclarationFDOBrisPorte $brouillon */
        $brouillon = $this->em->find(BrouillonDeclarationFDOBrisPorte::class, $id);

        /** @var DeclarationFDOBrisPorteInput $input */
        $input = $this->denormalizer->denormalize($brouillon->getDonnees(), DeclarationFDOBrisPorteInput::class, context: [AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false]);

        /** @var ConstraintViolationList $validation */
        $violations = $this->validator->validate($input);

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

        /** @var DeclarationFDOBrisPorte $declaration */
        $declaration = $this->objectMapper->map(
            $input,
            (new DeclarationFDOBrisPorte())
                ->setId($brouillon->getId())
                ->setDateCreation($brouillon->getDateCreation())
                ->setAgent($agent)
        );

        $this->em->persist($declaration);
        $this->em->remove($brouillon);
        $this->em->detach($brouillon);

        $this->em->flush();

        // Envoi du mail d'invitation à déclarer
        if (null !== ($coordonneesRequerant = $declaration->getCoordonneesRequerant())) {
            $this->mailer
                ->to($coordonneesRequerant->getCourriel(), $coordonneesRequerant->getPrenom().' '.$coordonneesRequerant->getNom())
                ->subject("Mon Indemnisation Justice: vous pouvez faire une demande d'indemnisation")
                ->htmlTemplate('email/invitation_a_deposer.html.twig', [
                    'declaration' => $declaration,
                ])
                ->send()
            ;
        }

        return new JsonResponse(
            $this->normalizer->normalize(
                $this->objectMapper->map($declaration, DeclarationFDOBrisPorteOutput::class),
                'json'
            ),
            Response::HTTP_CREATED
        );
    }
}
