<?php

namespace MonIndemnisationJustice\Service\Signature\Youtrust;

use Symfony\Component\Uid\Uuid;

/**
 * https://developers.youtrust.com/reference/post-signature_requests-1#response-schemas.
 */
class SignatureRequest
{
    protected Uuid $id;
    protected string $status;
    protected string $name;
    protected Uuid $documentId;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): SignatureRequest
    {
        $this->id = $id;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): SignatureRequest
    {
        $this->status = $status;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): SignatureRequest
    {
        $this->name = $name;
        return $this;
    }

    public function getDocumentId(): Uuid
    {
        return $this->documentId;
    }

    public function setDocumentId(Uuid $documentId): SignatureRequest
    {
        $this->documentId = $documentId;
        return $this;
    }
}
