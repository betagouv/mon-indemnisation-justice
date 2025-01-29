<?php

namespace MonIndemnisationJustice\Controller\Agent;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Administration;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Repository\AgentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[IsGranted(Agent::ROLE_AGENT_GESTION_PERSONNEL)]
#[Route('/agent/gestion')]
class GestionAgentsController extends AbstractController
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly AgentRepository $agentRepository,
    ) {
    }

    #[Route('/', name: 'gestion_agents_index', methods: ['GET'])]
    public function index(SerializerInterface $serializer): Response
    {
        return $this->render('agent/gestion_agents/index.html.twig', [
            'agents' => $serializer->serialize($this->agentRepository->getEnAttenteActivation(), 'json', [
                AbstractNormalizer::ATTRIBUTES => [
                    'id',
                    'nom',
                    'prenom',
                    'courriel' => 'email',
                    'administration' => fn (Administration $administration) => $administration->value,
                    'roles',
                    // 'company' => ['name'],
                ],
            ]),
            'administrations' => array_map(
                fn (Administration $administration) => [
                    'id' => $administration->value,
                    'libelle' => $administration->getLibelle(),
                    'estLibelleFeminin' => $administration->estLibelleFeminin(),
                    'roles' => $administration->getRolesEligibles(),
                ],
                Administration::cases()
            ),
        ]);
    }
}
