<?php

namespace MonIndemnisationJustice\Security\Oidc;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use GuzzleHttp\Exception\GuzzleException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ProConnectProvider extends AbstractProvider
{
    protected string $authorizationUrl;
    protected string $accessTokenUrl;
    protected string $userInfoUrl;
    protected array $jwks;

    public function __construct(
        array $options = [],
        array $collaborators = []
    ) {
        parent::__construct($options, $collaborators);

        try {
            $response = $this->httpClient->request('GET', $options['wellKnownUrl']);

            $configuration = json_decode($response->getBody()->getContents(), true);
            $this->authorizationUrl = $configuration['authorization_endpoint'];
            $this->accessTokenUrl = $configuration['token_endpoint'];
            $this->userInfoUrl = $configuration['userinfo_endpoint'];

            try {
                $response = $this->httpClient->get($configuration['jwks_uri']);

                $this->jwks = JWK::parseKeySet(
                    json_decode(
                        $response->getBody()->getContents(),
                        true
                    )
                );
            } catch (GuzzleException $e) {
                throw new AuthenticationException('Fetch of OIDC JWKs failed.');
            }
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
        return ['openid', 'given_name', 'usual_name', 'email'];
    }

    protected function checkResponse(ResponseInterface $response, $data) {}

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new GenericResourceOwner($response, $response['sub']);
    }

    protected function getAuthorizationHeaders($token = null)
    {
        return [
            'Authorization' => 'Bearer '.$token,
        ];
    }

    /**
     * Parses the response according to its content-type header.
     *
     * @return array
     *
     * @throws \UnexpectedValueException
     */
    protected function parseResponse(ResponseInterface $response)
    {
        $content = (string) $response->getBody();
        $type = $this->getContentType($response);

        if (false !== strpos($type, 'urlencoded')) {
            parse_str($content, $parsed);

            return $parsed;
        }

        // Attempt to parse the string as JSON regardless of content type,
        // since some providers use non-standard content types. Only throw an
        // exception if the JSON could not be parsed when it was expected to.
        try {
            if (json_validate($content)) {
                return $this->parseJson($content);
            }

            return (array) JWT::decode($content, $this->jwks);
        } catch (\UnexpectedValueException $e) {
            if (false !== strpos($type, 'json')) {
                throw $e;
            }

            if (500 == $response->getStatusCode()) {
                throw new \UnexpectedValueException(
                    'An OAuth server error was encountered that did not contain a JSON body',
                    0,
                    $e
                );
            }

            return $content;
        }
    }
}
