<?php

namespace MonIndemnisationJustice\Security\Authenticator;

use MonIndemnisationJustice\Entity\Administration;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Repository\AgentRepository;
use MonIndemnisationJustice\Repository\FournisseurIdentiteAgentRepository;
use MonIndemnisationJustice\Security\Oidc\OidcClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\HttpUtils;

class ProConnectAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        protected readonly HttpUtils $httpUtils,
        protected readonly string $loginPageRoute,
        protected readonly string $loginCheckRoute,
        protected readonly string $loginSuccessRoute,
        #[Autowire(service: 'oidc_client_pro_connect')]
        protected readonly OidcClient $oidcClient,
        protected readonly AgentRepository $agentRepository,
        protected readonly FournisseurIdentiteAgentRepository $fournisseurIdentiteAgentRepository,
        protected readonly UrlGeneratorInterface $urlGenerator,
        protected readonly LoggerInterface $logger,
        /** @var $autoPromotionHashes array liste de _hashes_ d'adresses courriel pour lesquelles le rôle
         * `ROLE_AGENT_GESTION_PERSONNEL` est automatiquement attribué
         */
        protected readonly ?array $autoPromotionHashes,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return
            $this->httpUtils->checkRequestPath($request, $this->loginCheckRoute)
            && $request->query->has('state')
                && (
                    $request->query->has('code')
                    || $request->query->has('error')
                )
        ;
    }

    public function authenticate(Request $request): Passport
    {
        try {
            // Authenticate
            list($accessToken) = $this->oidcClient->authenticate($request);

            // User info
            $userInfo = $this->oidcClient->fetchUserInfo($accessToken);

            $agent = $this->agentRepository->findOneBy(['identifiant' => $userInfo['sub']]);

            if (null === $agent) {
                $agent = ($this->agentRepository->findOneBy(['email' => $userInfo['email']]) ?? new Agent())
                ->setIdentifiant($userInfo['sub'])
                ->setEmail($userInfo['email'])
                ->setPrenom($userInfo['given_name'])
                ->setNom($userInfo['usual_name'])
                ->addRole(Agent::ROLE_AGENT)
                ->setUid($userInfo['uid'])
                ->setCree()
                ->setFournisseurIdentite($this->fournisseurIdentiteAgentRepository->find($userInfo['idp_id']))
                ->setDonnesAuthentification($userInfo)
                ;

                if (in_array(sha1($agent->getEmail()), $this->autoPromotionHashes ?? [])) {
                    $agent
                        ->addRole(Agent::ROLE_AGENT_GESTION_PERSONNEL)
                        ->addRole(Agent::ROLE_AGENT_REDACTEUR)
                        ->setAdministration(Administration::MINISTERE_JUSTICE)
                        ->setValide();
                }
            } else {
                $agent->setEmail($userInfo['email'])
                ->setPrenom($userInfo['given_name'])
                ->setNom($userInfo['usual_name']);

                // Rattrapage des donées 'custom' pour les agents connectés avant l'intégration de ces données
                // supplémentaires https://partenaires.proconnect.gouv.fr/docs/fournisseur-service/custom-scope
                if (
                    null !== $userInfo['custom']
                    && null !== ($donneesAuthentification = $agent->getDonnesAuthentification())
                    && !isset($donneesAuthentification['custom'])
                ) {
                    $agent->setDonnesAuthentification(
                        array_merge(
                            $donneesAuthentification,
                            $userInfo
                        )
                    );
                }
            }

            $this->agentRepository->save($agent);

            return new SelfValidatingPassport(new UserBadge($agent->getIdentifiant()));
        } catch (AuthenticationException $e) {
            $this->logger->error($e->getMessage(), $e->getMessageData());
            throw $e;
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->urlGenerator->generate($this->loginSuccessRoute));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new RedirectResponse($this->urlGenerator->generate($this->loginPageRoute, ['erreur' => 'proconnect']));
    }
}
