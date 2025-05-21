<?php

namespace MonIndemnisationJustice\Security\Oidc;

use Firebase\JWT\BeforeValidException;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\Cache\CacheInterface;

final class OidcConnectionContext
{
    public string $code;
    public string $nonce;
    public ?string $token;
}

class OidcClient
{
    protected HttpClient $client;
    protected ?array $configuration = null;
    /**
     * @var array<string, Key> the set of JSON Web Keys
     */
    protected ?array $jwks = null;

    public function __construct(
        protected readonly string $wellKnownUrl,
        protected readonly string $clientId,
        protected readonly string $clientSecret,
        protected readonly array $loginCheckRoutes,
        protected readonly UrlGeneratorInterface $urlGenerator,
        #[Target('oidc')] protected readonly CacheInterface $cache,
        protected readonly array $context = [],
        protected readonly ?string $logoutRoute = null,
    ) {
        $this->client = new HttpClient([]);
    }

    protected function configure(): void
    {
        if (null === $this->configuration) {
            $this->configuration = $this->cache->get('oidc_well_known_configuration', function () {
                try {
                    $response = $this->client->get($this->wellKnownUrl);

                    return json_decode($response->getBody()->getContents(), true);
                } catch (GuzzleException $e) {
                    throw new AuthenticationException('Fetch of OIDC server well known configuration failed.', previous: $e);
                }
            });
        }

        if (null === $this->jwks) {
            $this->jwks = JWK::parseKeySet($this->cache->get('oidc_jwks', function () {
                try {
                    $response = $this->client->get($this->configuration['jwks_uri']);

                    return json_decode(
                        $response->getBody()->getContents(),
                        true
                    );
                } catch (GuzzleException $e) {
                    throw new AuthenticationException('Fetch of OIDC JWKs failed.');
                }
            }));
        }
    }

    protected function getRedirectUri(?string $redirectRoute = null): string
    {
        return $this->urlGenerator->generate(null !== $redirectRoute && in_array($redirectRoute, $this->loginCheckRoutes) ? $redirectRoute : $this->loginCheckRoutes[0], referenceType: UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function buildAuthorizeUrl(Request $request, ?string $redirectRoute = null): string
    {
        $this->configure();

        $state = Uuid::uuid4()->toString();
        $nonce = Uuid::uuid4()->toString();
        $redirectUri = $this->getRedirectUri($redirectRoute);

        $request->getSession()->set('_oidc_authentication', [
            'state' => $state,
            'nonce' => $nonce,
            'redirect_uri' => $redirectUri,
        ]);

        return sprintf(
            '%s?%s',
            $this->configuration['authorization_endpoint'],
            http_build_query(
                array_merge(
                    [
                        'response_type' => 'code',
                        'client_id' => $this->clientId,
                        'redirect_uri' => $redirectUri,
                        'state' => $state,
                        'nonce' => $nonce,
                    ],
                    $this->context,
                    [
                        'scope' => implode(' ', $this->context['scope'] ?? []),
                    ]
                )
            )
        );
    }

    public function buildLogoutUrl(Request $request, string $idToken): string
    {
        $state = Uuid::uuid4()->toString();
        $redirectUri = $this->urlGenerator->generate($this->logoutRoute, referenceType: UrlGeneratorInterface::ABSOLUTE_URL);

        $request->getSession()->set('_oidc_authentication', [
            'state' => $state,
        ]);

        return sprintf(
            '%s?%s',
            $this->configuration['end_session_endpoint'],
            http_build_query(
                [
                    'id_token_hint' => $idToken,
                    'post_logout_redirect_uri' => $redirectUri,
                    'state' => $state,
                ]
            )
        );
    }

    /**
     * @return string[]
     */
    public function authenticate(Request $request): array
    {
        $this->configure();

        if (null !== $error = $request->query->get('error')) {
            throw new AuthenticationException("$error - ".$request->query->get('error_description'));
        }

        $context = $request->getSession()->get('_oidc_authentication', []);
        $state = $request->query->get('state');
        $code = $request->query->get('code');

        if ($state !== ($context['state'] ?? null)) {
            throw new AuthenticationException('Invalid state.');
        }

        try {
            $response = $this->client->post($this->configuration['token_endpoint'], [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'redirect_uri' => $context['redirect_uri'],
                    'code' => $code,
                ],
            ]);
        } catch (RequestException $e) {
            $context = json_decode($e->getResponse()->getBody()->getContents());
            throw new AuthenticationException("$context->error - $context->error_description", previous: $e);
        } catch (GuzzleException $e) {
            throw new AuthenticationException('Authorization failed.', previous: $e);
        }

        $credentials = json_decode($response->getBody()->getContents());
        $accessToken = $credentials->access_token ?? null;
        try {
            $idToken = JWT::decode($credentials->id_token, $this->jwks);
        } catch (SignatureInvalidException|BeforeValidException) {
            throw new AuthenticationException('Authorization failed (invalid id token).');
        }

        if ($idToken->nonce !== $context['nonce']) {
            throw new AuthenticationException('Authorization failed (nonce does not match).');
        }

        return [$accessToken, $credentials->id_token];
    }

    public function fetchUserInfo(string $token): array
    {
        $this->configure();

        $response = $this->client->get($this->configuration['userinfo_endpoint'], [
            'headers' => [
                'Authorization' => "Bearer $token",
            ],
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new AuthenticationException('User info fetching failed.');
        }

        // Si les données utilisateurs renvoyées sont au format JSON, on les renvoie décodées
        if (json_validate($raw = $response->getBody()->getContents())) {
            return json_decode($raw, true);
        }

        // Sinon, on traite en JWT (au risque de jeter des exceptions)
        return (array) JWT::decode($raw, $this->jwks);
    }

    public function logout(Request $request): bool
    {
        $context = $request->getSession()->get('_oidc_authentication', []);
        $state = $request->query->get('state');

        if ($state === ($context['state'] ?? null)) {
            $request->getSession()->remove('_oidc_authentication');

            return true;
        }

        return false;
    }
}
