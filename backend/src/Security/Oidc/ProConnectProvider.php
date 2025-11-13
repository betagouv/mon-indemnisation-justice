<?php

namespace MonIndemnisationJustice\Security\Oidc;

use GuzzleHttp\Exception\GuzzleException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ProConnectProvider extends AbstractProvider
{
    protected string $authorizationUrl;
    protected string $accessTokenUrl;
    protected string $userInfoUrl;

    public function __construct(string $wellKnownURL)
    {
        parent::__construct();

        try {
            $response = $this->httpClient->request('GET', $wellKnownURL);

            $configuration = json_decode($response->getBody()->getContents(), true);
            $this->authorizationUrl = $configuration['authorization_endpoint'];
            $this->accessTokenUrl = $configuration['token_endpoint'];
            $this->userInfoUrl = $configuration['userinfo_endpoint'];
        } catch (GuzzleException $e) {
            throw new AuthenticationException('Fetch of OIDC server well known configuration failed.', previous: $e);
        }
    }

    public function getBaseAuthorizationUrl()
    {
        return $this->authorizationUrl;
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->accessTokenUrl;
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->userInfoUrl;
    }

    protected function getDefaultScopes()
    {
        // TODO: Implement getDefaultScopes() method.
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        // TODO: Implement checkResponse() method.
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        // TODO: Implement createResourceOwner() method.
    }
}
