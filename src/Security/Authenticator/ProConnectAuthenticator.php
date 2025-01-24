<?php

namespace MonIndemnisationJustice\Security\Authenticator;

use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Repository\AgentRepository;
use MonIndemnisationJustice\Repository\FournisseurIdentiteAgentRepository;
use MonIndemnisationJustice\Security\Oidc\OidcClient;
use Psr\Log\LoggerInterface;
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
        protected readonly OidcClient $oidcClient,
        protected readonly AgentRepository $agentRepository,
        protected readonly FournisseurIdentiteAgentRepository $fournisseurIdentiteAgentRepository,
        protected readonly UrlGeneratorInterface $urlGenerator,
        protected readonly LoggerInterface $logger,
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
            $token = $this->oidcClient->authenticate($request);

            // User info
            $userInfo = $this->oidcClient->fetchUserInfo($token);

            $agent = $this->agentRepository->findOneBy(['identifiant' => $userInfo['sub']]);

            if (null === $agent) {
                $agent = (new Agent())
                ->setIdentifiant($userInfo['sub'])
                ->setEmail($userInfo['email'])
                ->setPrenom($userInfo['usual_name'])
                ->setNom($userInfo['given_name'])
                ->addRole(Agent::ROLE_AGENT)
                ->setUid($userInfo['uid'])
                ->setFournisseurIdentite($this->fournisseurIdentiteAgentRepository->find($userInfo['idp_id']))
                ->setDonnesAuthentification($userInfo)
                ;

                $this->agentRepository->save($agent);
            }

            return new SelfValidatingPassport(new UserBadge($agent->getUserIdentifier()));
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
