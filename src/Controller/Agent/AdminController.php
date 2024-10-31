<?php

namespace App\Controller\Agent;

use App\Entity\Agent;
use App\Repository\AgentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Agent::ROLE_AGENT_GESTION_PERSONNEL)]
#[Route('/agent/gestion')]
class AdminController extends AbstractController
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly AgentRepository $agentRepository,
    ) {
    }

    #[Route('/', name: 'app_admin_homepage')]
    public function index(): Response
    {
        $agents = $this->agentRepository->findByRoles([
            Agent::ROLE_AGENT_VALIDATEUR,
            Agent::ROLE_AGENT_REDACTEUR,
        ]);

        return $this->render('admin/default/index.html.twig', [
            'agents' => $agents,
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
