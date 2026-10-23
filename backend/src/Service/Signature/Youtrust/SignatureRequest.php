<?php

namespace MonIndemnisationJustice\Service\Signature\Youtrust;

use Symfony\Component\Uid\Uuid;

/**
 * https://developers.youtrust.com/reference/post-signature_requests-1#response-schemas.
 */
readonly class SignatureRequest
{
    public function __construct(
        public Uuid $id,
        public string $status,
        public string $name,
        public Uuid $documentId,
    ) {
    }
}
