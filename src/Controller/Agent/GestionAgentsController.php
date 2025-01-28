<?php

namespace MonIndemnisationJustice\Controller\Agent;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Administration;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Repository\AgentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
    public function index(): Response
    {
        return $this->render('agent/gestion_agents/index.html.twig', [
            'agents' => $this->agentRepository->getEnAttenteActivation(),
            'administrations' => Administration::cases(),
            'roles' => Administration::getRolesParAdministration(),
        ]);
    }

    #[Route('/activer/{id}', name: 'agent_gestionnaire_activer_agent', methods: ['POST'])]
    public function activerAgent(int $id, Request $request): Response
    {
        $agent = $this->em->getRepository(Agent::class)->find($id);

        if (null === $agent) {
            throw new NotFoundHttpException('Aucun agent trouvé');
        }

        if (!$this->isCsrfTokenValid('activation_agent', $request->request->get('_csrf'))) {
            throw new BadRequestException('Jeton CSRF manquant');
        }

        $agent->setActive($request->request->has('agentActif'));
        $this->em->persist($agent);
        $this->em->flush();

        return $this->redirectToRoute('app_admin_homepage');
    }
}
