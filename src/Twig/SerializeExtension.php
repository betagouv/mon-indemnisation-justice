<?php

namespace MonIndemnisationJustice\Twig;

use Symfony\Component\Serializer\SerializerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class SerializeExtension extends AbstractExtension
{
    private ?SerializerInterface $serializer=null;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
    public function getFilters(): array
    {
        return [
            new TwigFilter('serialize', [$this, 'serialize']),
        ];
    }

    public function serialize($data, string $format = 'json', array $context = []): string
    {
        return $this->serializer->serialize($data, $format, $context);
    }
}
