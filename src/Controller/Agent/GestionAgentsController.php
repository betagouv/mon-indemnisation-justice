<?php

namespace MonIndemnisationJustice\Controller\Agent;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Dto\NouvelAgentDto;
use MonIndemnisationJustice\Entity\Administration;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Repository\AgentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
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

    protected function normalizeAgent(Agent $agent): array
    {
        return [
            'id' => $agent->getId(),
            'nom' => $agent->getNom(),
            'prenom' => $agent->getPrenom(),
            'courriel' => $agent->getEmail(),
            'administration' => $agent->getAdministration()?->value,
            'roles' => $agent->getRoles(),
            'datePremiereConnexion' => $agent->getDateCreation() ? (int) $agent->getDateCreation()->format('Uv') : null,
        ];
    }

    /**
     * @param Agent[] ...$agents
     */
    protected function normalizeAgents(...$agents): array
    {
        return array_map(
            fn (Agent $agent) => $this->normalizeAgent($agent), ...$agents);
    }

    /**
     * @param Administration[] ...$administrations
     */
    protected function normalizeAdministration(...$administrations): array
    {
        return array_map(
            fn (Administration $administration) => [
                'id' => $administration->value,
                'libelle' => $administration->getLibelle(),
                'estLibelleFeminin' => $administration->estLibelleFeminin(),
                'roles' => $administration->getRolesEligibles(),
            ], ...$administrations);
    }

    #[Route('', name: 'gestion_agents_index', methods: ['GET'])]
    public function index(SerializerInterface $serializer): Response
    {
        return $this->render('agent/gestion_agents/index.html.twig', [
            'react' => [
                'agents' => $this->normalizeAgents($this->agentRepository->getEnAttenteActivation()),
                'administrations' => $this->normalizeAdministration(Administration::cases()),
            ],
        ]);
    }

    #[Route('/nouvel-agent.json', name: 'gestion_agents_nouvel_agent_json', methods: ['POST'])]
    public function nouvelAgent(
        #[MapRequestPayload(acceptFormat: 'json')] NouvelAgentDto $nouvelAgentDto,
    ) {
        if (null !== $this->agentRepository->findOneBy(['email' => $nouvelAgentDto->courriel])) {
            return new JsonResponse([
                'courriel' => 'Cette adresse est déjà attribuée à un agent',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $agent = (new Agent())
            ->setPrenom($nouvelAgentDto->prenom ?? '')
            ->setNom($nouvelAgentDto->nom ?? '')
            ->setEmail($nouvelAgentDto->courriel)
            ->setIdentifiant($nouvelAgentDto->courriel)
            ->setUid($nouvelAgentDto->courriel)
            ->setRoles([Agent::ROLE_AGENT])
        ;

        $this->agentRepository->save($agent);

        return new JsonResponse($this->normalizeAgent($agent), Response::HTTP_CREATED);
    }

    #[Route('.json', name: 'gestion_agents_submit_json', methods: ['POST'])]
    public function submit(Request $request, NormalizerInterface $normalizer): Response
    {
        foreach ($request->getPayload() as $id => $config) {
            $agent = $this->agentRepository->find($id);
            $administration = Administration::from($config['administration']);
            $roles = $config['roles'];

            $agent
                ->setValide()
                ->setAdministration($administration)
                ->setRoles($roles);

            $this->agentRepository->save($agent);
        }

        return new JsonResponse([
            'agents' => $this->normalizeAgents($this->agentRepository->getEnAttenteActivation()),
        ]);
    }
}
