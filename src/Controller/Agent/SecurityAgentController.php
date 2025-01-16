<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller\Agent;

use MonIndemnisationJustice\Dto\ModificationMotDePasse;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Forms\ModificationMotDePasseType;
use MonIndemnisationJustice\Repository\AgentRepository;
use MonIndemnisationJustice\Security\Oidc\OidcClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/agent')]
class SecurityAgentController extends AbstractController
{
    public function __construct(
        protected readonly AuthenticationUtils $authenticationUtils,
        protected UserPasswordHasherInterface $userPasswordHasher,
        protected readonly AgentRepository $agentRepository,
        protected readonly OidcClient $oidcClient,
    ) {
    }

    #[Route('/se-connecter', name: 'agent_securite_se_connecter', methods: ['GET', 'POST'])]
    #[IsGranted('PUBLIC_ACCESS')]
    public function seConnecter(Request $request): Response
    {
        if ($this->getUser() instanceof Agent) {
            return $this->redirectToRoute('agent_index');
        }

        if ($request->isMethod(Request::METHOD_POST)) {
            return $this->redirect($this->oidcClient->buildAuthorizeUrl($request));
        }

        return $this->render('agent/connexion.html.twig', [
            'title' => "Connexion à l'espace agent",
            'last_username' => $this->authenticationUtils->getLastUsername(),
            'error' => $this->authenticationUtils->getLastAuthenticationError(),
        ]);
    }


    #[Route('/connexion', name: 'agent_securite_connexion', methods: ['GET'])]
    #[IsGranted('PUBLIC_ACCESS')]
    public function connexion(Request $request): Response
    {
        return $this->render('agent/connexion.html.twig', [
            'title' => "Connexion à l'espace agent",
            'last_username' => $this->authenticationUtils->getLastUsername(),
            'error' => $this->authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route(path: '/deconnexion', name: 'agent_securite_deconnexion')]
    public function logout(): void
    {
        throw new \LogicException("Impossible de déconnecter l'agent");
    }

    #[Route(path: '/activation/{jeton}', name: 'app_agent_securite_activation', methods: ['GET', 'POST'])]
    public function activation(Request $request, string $jeton): Response
    {
        $agent = $this->agentRepository->findOneBy(['jetonVerification' => $jeton]);

        if (null === $agent) {
            return $this->redirectToRoute('agent_securite_se_connecter');
        }

        $modificationMotDePasse = new ModificationMotDePasse();

        $form = $this->createForm(ModificationMotDePasseType::class, $modificationMotDePasse);
        $errors = [];

        if (Request::METHOD_POST === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    $modificationMotDePasse = $form->getData();

                    $agent->setPassword(
                        $this->userPasswordHasher->hashPassword(
                            $agent,
                            $modificationMotDePasse->motDePasse
                        )
                    );
                    $agent->supprimerJetonVerification();

                    $this->agentRepository->save($agent);

                    return $this->redirectToRoute('agent_securite_se_connecter');
                } else {
                    /** @var FormError $error */
                    foreach ($form->getErrors(true) as $key => $error) {
                        $errors[$error->getOrigin()?->getName()] = $error->getMessage();
                    }
                }
            }
        }

        return $this->render('agent/activation.html.twig', [
            'agent' => $agent,
            'form' => $form,
            'errors' => $errors,
        ]);
    }
}
