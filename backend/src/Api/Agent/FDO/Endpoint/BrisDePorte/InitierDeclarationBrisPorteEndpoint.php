<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\FDO\Dto\DeclarationFDOBrisPorteDto;
use MonIndemnisationJustice\Api\Agent\FDO\Voter\DeclarationFDOBrisPorteVoter;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Route API qui permet à un agent des FDO d'initier le brouillon d'une déclaration d'erreur opérationnelle.
 */
#[Route('/api/agent/fdo/bris-de-porte/initier', name: 'api_agent_fdo_bris_porte_initier', methods: ['PUT'])]
#[IsGranted(
    DeclarationFDOBrisPorteVoter::ACTION_DECLARER,
    message: "La déclaration d'une erreur opérationnelle est retreinte aux agents des Forces de l'Ordre",
    statusCode: Response::HTTP_FORBIDDEN
)]
#[Serialize(code: Response::HTTP_CREATED)]
class InitierDeclarationBrisPorteEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    public function __invoke(Security $security): DeclarationFDOBrisPorteDto
    {
        /** @var Agent $agent */
        $agent = $security->getUser();

        $declaration = new DeclarationFDOBrisPorte()
            ->setAgent($agent);

        $this->em->persist($declaration);
        $this->em->flush();

        return DeclarationFDOBrisPorteDto::depuisDeclaration($declaration);
    }
}
