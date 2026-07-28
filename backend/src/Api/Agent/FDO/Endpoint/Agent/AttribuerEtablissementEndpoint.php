<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Endpoint\Agent;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\FDO\Output\MoiOutput;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\FDO\EtablissementFDO;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/agent/fdo/affecter', name: 'api_agent_fdo_affecter', methods: ['POST'])]
#[IsGranted(
    Agent::ROLE_AGENT_FORCES_DE_L_ORDRE,
    message: "Vous devez être connecté en tant qu'agent des Forces de l'Ordre",
    statusCode: Response::HTTP_FORBIDDEN
)]
class AttribuerEtablissementEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(
        #[MapRequestPayload]
        AttribuerEtablissementInput $input,
        Security $security,
        Request $request,
    ) {
        /** @var Agent $agent */
        $agent = $security->getUser();

        if ($input->estExempt) {
            $agent->setExempteAffectation(true);
        } else {
            $etablissement = $input->etablissement ? $this->em->getRepository(EtablissementFDO::class)->find($input->etablissement) : null;
            if (null === $etablissement) {
                throw new BadRequestHttpException('Etablissement inconnu');
            }

            $agent->affecter($etablissement, \DateTimeImmutable::createFromMutable($input->dateAffectation));
        }

        $this->em->persist($agent);
        $this->em->flush();

        return new JsonResponse(
            $this->normalizer->normalize(
                MoiOutput::depuisAgent($agent),
                'json'
            )
        );
    }
}
