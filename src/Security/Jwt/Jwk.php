<?php

namespace MonIndemnisationJustice\Security\Jwt;

enum JwkKeyType: string
{
    case RSA = 'RSA';
    case EC = 'EC';
}

enum JwkUseType: string
{
    case SIG = 'sig';
    case ENC = 'enc';
}

enum JwkEncryptionAlgorithm: string
{
    case ES256 = 'ES256';
    case RS256 = 'RS256';
}

/**
 * JSON Web Key (see [official RFC](https://datatracker.ietf.org/doc/html/rfc7517)).
 */
class Jwk
{
    /**
     * @var string the Key Type parameter
     */
    public readonly JwkKeyType $kty;
    public readonly JwkUseType $use;
    public readonly JwkEncryptionAlgorithm $alg;
    /**
     * @var string the key ID
     */
    public readonly string $kid;
    public readonly array $data;

    public static function fromArray(array $values): Jwk
    {
        $jwk = new Jwk();
        $jwk->kty = JwkKeyType::from($values['kty']);
        $jwk->use = JwkUseType::from($values['use']);
        $jwk->alg = JwkEncryptionAlgorithm::from($values['alg']);
        $jwk->kid = $values['kid'];
        // Send as key data the other values
        $jwk->data = array_filter($values, function ($k) {
            return !in_array($k, ['kty', 'use', 'alg', 'kid']);
        }, ARRAY_FILTER_USE_KEY);

        return $jwk;
    }
}
