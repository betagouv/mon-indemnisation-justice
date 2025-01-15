<?php

namespace MonIndemnisationJustice\Security\Oidc;

use Drenso\OidcBundle\Exception\OidcException;
use Drenso\OidcBundle\Model\OidcTokens;
use Drenso\OidcBundle\Model\OidcUserData;
use Drenso\OidcBundle\OidcClient;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Lcobucci\JWT\JwtFacade;

class JwtBasedOidcClient extends OidcClient
{
    public function retrieveUserInfo(OidcTokens $tokens): OidcUserData
    {
        // Set the authorization header
        $headers = ["Authorization: Bearer {$tokens->getAccessToken()}"];

        // Retrieve the user information and convert the encoding to UTF-8 to harden for surfconext UTF-8 bug
        $jwtData = $this->urlFetcher->fetchUrl($this->getUserinfoEndpoint(), null, $headers);
        $jwtData = mb_convert_encoding($jwtData, 'UTF-8');

        $decoded = JWT::decode($jwtData, new Key('', 'RS256'));


        dump($decoded);

        return new OidcUserData((array) $decoded);
    }
}
