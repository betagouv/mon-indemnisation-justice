<?php

namespace MonIndemnisationJustice\Security\Oidc;

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

    public function __construct(
        protected readonly string $wellKnownUrl,
        protected readonly string $clientId,
        protected readonly string $clientSecret,
        protected readonly string $loginCheckRoute,
        protected readonly UrlGeneratorInterface $urlGenerator,
        #[Target('oidc')] protected readonly CacheInterface $cache,
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
                    throw new AuthenticationException('Fetch of OIDC server well known configuration failed.');
                }
            });
        }
    }

    protected function getRedirectUri(): string
    {
        return $this->urlGenerator->generate($this->loginCheckRoute, referenceType: UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function buildAuthorizeUrl(Request $request): string
    {
        $this->configure();

        $state = Uuid::uuid4()->toString();
        $nonce = Uuid::uuid4()->toString();

        $request->getSession()->set('_oidc_authentication', [
            'state' => $state,
            'nonce' => $nonce,
        ]);

        return sprintf(
            '%s?%s',
            $this->configuration['authorization_endpoint'],
            http_build_query([
                'response_type' => 'code',
                'client_id' => $this->clientId,
                'redirect_uri' => $this->getRedirectUri(),
                'scope' => implode(' ', ['openid', 'given_name', 'usual_name', 'email', 'uid']),
                'state' => $state,
                'nonce' => $nonce,
            ])
        );
    }

    public function authenticate(Request $request): string
    {
        $this->configure();

        $state = $request->query->get('state');
        $code = $request->query->get('code');

        $context = $request->getSession()->get('_oidc_authentication', []);

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
                    'redirect_uri' => $this->getRedirectUri(),
                    'code' => $code,
                ],
            ]);
        } catch (RequestException $e) {
            exit($e->getResponse()->getBody()->getContents());
        } catch (GuzzleException $e) {
            dump($e->getMessage(), $e->getTraceAsString());
            throw new AuthenticationException('Authorization failed.', previous: $e);
        }

        $credentials = json_decode($response->getBody()->getContents());
        $accessToken = $credentials->access_token ?? null;
        $idToken = $credentials->id_token ?? null;

        // TODO verifier le JWT et le nonce

        return $accessToken;
    }

    public function fetchUserInfo(string $token): object
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

        $jwt = $response->getBody()->getContents();

        list($header, $payload, $signature) = explode('.', $jwt);

        return json_decode(base64_decode($payload));
    }
}
