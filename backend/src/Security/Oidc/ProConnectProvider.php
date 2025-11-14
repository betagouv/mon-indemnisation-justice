<?php

namespace MonIndemnisationJustice\Security\Oidc;

use Firebase\JWT\JWT;
use GuzzleHttp\Exception\GuzzleException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\Cache\CacheInterface;

class ProConnectProvider extends AbstractProvider
{
    protected ?string $authorizationUrl = null;
    protected ?string $accessTokenUrl = null;
    protected ?string $userInfoUrl = null;
    protected ?array $jwks = null;

    public function __construct(
        #[Autowire('%env(PRO_CONNECT_WELL_KNOWN_URL)%')]
        protected readonly string $wellKnownUrl,
        #[Target('oidc')]
        protected readonly CacheInterface $cache,
    ) {
        parent::__construct();
    }

    public function getBaseAuthorizationUrl()
    {
        $this->configure();

        return $this->authorizationUrl;
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        $this->configure();

        return $this->accessTokenUrl;
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        $this->configure();

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

    protected function get(string $url, string $errorMessage): array
    {
        try {
            $response = $this->httpClient->get($url);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new AuthenticationException($errorMessage, previous: $e);
        }
    }

    protected function configure(): void
    {
        if (null === $this->authorizationUrl) {
            list($this->authorizationUrl, $this->accessTokenUrl, $this->userInfoUrl, $this->jwks) = $this->cache->get(
                sprintf('_oidc_well_known_configuration_%s', sha1($this->wellKnownUrl)),
                function () {
                    $configuration = $this->get($this->wellKnownUrl, "La configuration ProConnect n'a pu être initialisée (erreur lors de la découverte de la configuration)");
                    $jwks = $this->get($configuration['jwks_uri'], "La configuration ProConnect n'a pu être initialisée (erreur lors de la lecture des clefs de chiffrement)");

                    return [$configuration['authorization_endpoint'], $configuration['token_endpoint'], $configuration['userinfo_endpoint'], $jwks];
                }
            );
        }
    }
}
