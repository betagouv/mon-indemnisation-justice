<?php

namespace App\Service;

use App\Entity\Document;
use Aws\S3\S3Client;

class DocumentManager
{
    public function __construct(
        protected readonly S3Client $client,
        protected readonly string $bucket
    ) {}

    public function getDocumentBody(Document $document): ?string
    {
        $file = $this->client->getObject([
          'Bucket' => $this->bucket,
          'Key' => $document->getFilename(),
        ]);
        $body = $file->get('Body');
        $body->rewind();

        return $body->getContents();
    }
}