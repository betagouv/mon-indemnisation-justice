<?php

namespace MonIndemnisationJustice\Security\Oidc;

use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use Symfony\Component\HttpFoundation\RequestStack;

class ProConnectClient extends OAuth2Client
{
    public function __construct(
        ProConnectProvider $provider,
        RequestStack $requestStack
    ) {
        parent::__construct($provider, $requestStack);
    }
}
