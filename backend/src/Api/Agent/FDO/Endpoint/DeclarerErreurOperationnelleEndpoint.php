<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Endpoint;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\FDO\Input\DeclarationErreurOperationnelleInput;
use MonIndemnisationJustice\Api\Agent\FDO\Transformers\DeclarationErreurOperationnelleOutputMapper;
use MonIndemnisationJustice\Api\Agent\FDO\Voter\DeclarationErreurOperationelleVoter;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\DeclarationErreurOperationnelle;
use MonIndemnisationJustice\Service\Mailer;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui permet à un agent des FDO de déclarer une erreur opérationnelle.
 */
#[Route('/api/agent/fdo/erreur-operationnelle/declarer', name: 'api_agent_fdo_erreur_operationnelle_declarer', methods: ['PUT'])]
#[IsGranted(
    DeclarationErreurOperationelleVoter::ACTION_DECLARER,
    message: "La déclaration d'une erreur opérationnelle est retreinte aux agents des Forces de l'Ordre",
    statusCode: Response::HTTP_FORBIDDEN
)]
class DeclarerErreurOperationnelleEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ObjectMapperInterface $objectMapper,
        protected readonly NormalizerInterface $normalizer,
        protected readonly Mailer $mailer,
    ) {}

    public function __invoke(#[MapRequestPayload] DeclarationErreurOperationnelleInput $input, Security $security): Response
    {
        /** @var Agent $agent */
        $agent = $security->getUser();
        // On en profite pour mettre à jour le numéro de téléphone de l'agent avec la valeur fournie
        if ($agent->getTelephone() !== $input->telephone) {
            $agent->setTelephone($input->telephone);
            $this->em->persist($agent);
        }
        $declaration = $this->objectMapper->map($input, DeclarationErreurOperationnelle::class)->setAgent($agent);

        $this->em->persist($declaration);
        $this->em->flush();

        // Envoi du mail d'invitation à déclarer
        if (null !== ($infosRequerants = $declaration->getInfosRequerant())) {
            $this->mailer
                ->to($infosRequerants->courriel, $infosRequerants->prenom.' '.$infosRequerants->nom)
                ->subject("Mon Indemnisation Justice: vous pouvez faire une demande d'indemnisation")
                ->htmlTemplate('email/invitation_a_deposer.html.twig', [
                    'declaration' => $declaration,
                ])
                ->send()
            ;
        }

        return new JsonResponse([
            $this->normalizer->normalize(
                DeclarationErreurOperationnelleOutputMapper::mapper($declaration, $this->objectMapper),
                'json'
            ),
        ], Response::HTTP_CREATED);
    }
}
