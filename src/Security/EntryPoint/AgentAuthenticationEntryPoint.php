<?php

namespace MonIndemnisationJustice\Security\EntryPoint;

use ApiPlatform\Metadata\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class AgentAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(protected readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function start(Request $request, ?AuthenticationException $authException = null): RedirectResponse
    {
        dump('Ici');

        return new RedirectResponse($this->urlGenerator->generate('agent_securite_se_connecter'));
    }
}
