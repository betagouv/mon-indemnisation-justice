<?php

namespace MonIndemnisationJustice\Security\Authenticator;

use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Repository\AgentRepository;
use MonIndemnisationJustice\Security\Oidc\OidcClient;
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
        protected readonly string $loginCheckRoute,
        protected readonly string $loginSuccessRoute,
        protected readonly OidcClient $oidcClient,
        protected readonly AgentRepository $agentRepository,
        protected readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return
            $this->httpUtils->checkRequestPath($request, $this->loginCheckRoute)
            && $request->query->has('code')
            && $request->query->has('state')
        ;
    }

    public function authenticate(Request $request): Passport
    {
        // Authenticate
        $token = $this->oidcClient->authenticate($request);

        // User info
        $userInfo = $this->oidcClient->fetchUserInfo($token);

        /*
         * "sub": "797ab77a-20e0-441f-a6bd-0a6974b02c82"
         * "given_name": "Pierre"
         * "usual_name": "Lemée"
         * "email": "pierre.lemee@beta.gouv.fr"
         * "uid": "7271"
         *
         */

        // TODO rechercher sur le `sub` (à créer) qui est immutable
        $agent = $this->agentRepository->findOneBy(['email' => $userInfo->email]);

        if (null === $agent) {
            $agent = (new Agent())
            ->setEmail($userInfo->email)
            ->setPrenom($userInfo->usual_name)
            ->setNom($userInfo->given_name)
            ->addRole(Agent::ROLE_AGENT)
            ->setActive(true);

            $this->agentRepository->save($agent);
        }

        return new SelfValidatingPassport(new UserBadge($agent->getUserIdentifier()));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->urlGenerator->generate($this->loginSuccessRoute));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return null;
    }
}
