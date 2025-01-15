<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller\Agent;

use Drenso\OidcBundle\OidcClientInterface;
use Drenso\OidcBundle\OidcClientLocator;
use MonIndemnisationJustice\Dto\ModificationMotDePasse;
use MonIndemnisationJustice\Forms\ModificationMotDePasseType;
use MonIndemnisationJustice\Repository\AgentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
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
        #[Target('drenso.oidc.client.pro_connect')] protected readonly OidcClientInterface $proConnectClient
    ) {
    }

    #[Route('/se-connecter', name: 'agent_securite_se_connecter', methods: ['GET', 'POST'])]
    #[IsGranted('PUBLIC_ACCESS')]
    public function seConnecter(Request $request, OidcClientLocator $oidcClientLocator): Response
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            return $this->proConnectClient->generateAuthorizationRedirect();
        }

        return $this->render('agent/connexion.html.twig', [
            'title' => "Connexion à l'espace agent",
            'last_username' => $this->authenticationUtils->getLastUsername(),
            'error' => $this->authenticationUtils->getLastAuthenticationError(),
        ]);
    }


    #[Route('/connexion', name: 'agent_securite_connexion', methods: ['GET'])]
    #[IsGranted('PUBLIC_ACCESS')]
    public function connexion(Request $request, OidcClientLocator $oidcClientLocator): Response
    {
        return $this->render('agent/connexion.html.twig', [
            'title' => "Connexion à l'espace agent",
            'last_username' => $this->authenticationUtils->getLastUsername(),
            'error' => $this->authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route(path: '/deconnexion', name: 'app_agent_securite_deconnexion')]
    public function logout(): void
    {
        throw new \LogicException("Impossible de déconnecter l'agent");
    }

    #[Route(path: '/activation/{jeton}', name: 'app_agent_securite_activation', methods: ['GET', 'POST'])]
    public function activation(Request $request, string $jeton): Response
    {
        $agent = $this->agentRepository->findOneBy(['jetonVerification' => $jeton]);

        if (null === $agent) {
            return $this->redirectToRoute('app_agent_securite_connexion');
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

                    return $this->redirectToRoute('app_agent_securite_connexion');
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
