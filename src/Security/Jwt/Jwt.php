<?php

namespace MonIndemnisationJustice\Security\Jwt;

use Firebase\JWT\JWK as FirebaseJWK;
use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\SignatureInvalidException;

class Jwt
{
    protected readonly string $value;
    protected readonly array $header;
    protected readonly array $payload;
    protected readonly string $message;
    protected readonly string $signature;

    public function __construct(
        string $value,
    ) {
        $this->value = $value;
        list($header, $payload, $signature) = explode('.', $value);

        $this->header = json_decode(base64_decode(urldecode($header)), true);
        $this->payload = json_decode(base64_decode(urldecode($payload)), true);
        $this->message = "{$header}.{$payload}";
        $this->signature = $signature;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getValue(string $name): mixed
    {
        return $this->payload[$name] ?? null;
    }

    protected function extractJwkForAlgo(array $jwks, string $kid)
    {
        $key = array_search($kid, array_column($jwks, 'kid'));

        return $jwks[$key];
    }

    protected function buildPem(array $jwk): string
    {
        // Ça ne fonctionne pas, voire vendor/firebase/php-jwt/src/JWK.php:231

        return "-----BEGIN PUBLIC KEY-----\n".
        chunk_split(base64_encode('MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA'.strtr($jwk['n'].'ID'.$jwk['e'], '-_', '+/')), 64).
         '-----END PUBLIC KEY-----';
    }

    public function verify(array $jwks): bool
    {
        $jwk = $this->extractJwkForAlgo($jwks, $this->header['kid']);

        try {
            /*
            return 1 === openssl_verify(
            $this->message,
            base64_decode($this->signature),
            $this->buildPem($jwk))
            OPENSSL_ALGO_SHA256
            ); */
            FirebaseJWT::decode($this->value, FirebaseJWK::parseKey($jwk));

            return true;
        } catch (SignatureInvalidException) {
            return false;
        }
    }

    private static function base64Encode(string $value): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($value));
    }

    public static function parse(string $jwt): Jwt
    {
        return new Jwt($jwt);
    }
}
