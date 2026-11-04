<?php

namespace MonIndemnisationJustice\Service\Brevo;

use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

class TransactionalWebhookPayloadDto
{
    #[Assert\NotBlank]
    public string $event;

    #[SerializedName('ts_event')]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'U'])]
    #[Assert\DateTime]
    #[Assert\GreaterThanOrEqual('2026-01-01')]
    public \DateTimeImmutable $tsEvent;

    #[SerializedName('X-Mailin-custom')]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    /**
     * On récupère l'`EnvoiCourriel` par son id via `X-Mailin-Custom` https://developers.brevo.com/docs/transactional-webhooks#sent.
     * */
    public ?string $idCourriel = null;

    #[SerializedName('user_agent')]
    public ?string $userAgent = null;

    #[SerializedName('device_used')]
    public ?string $deviceUsed = null;
}
