<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Output;

use MonIndemnisationJustice\Entity\AffectationAgentFDO;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class AffectationAgentFDOOutput
{
    public function __construct(
        public string $id,
        public EtablissementFDOOutput $etablissement,
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
        public \DateTimeImmutable $dateAffectation,
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
        public ?\DateTimeImmutable $dateMutation = null,
    ) {

    }

    public static function depuisAffectation(AffectationAgentFDO $affectation): self
    {
        return new self(
            id: "{$affectation->getAgent()->getId()}-{$affectation->getEtablissement()->getId()}",
            etablissement: EtablissementFDOOutput::depuisEtablissementFDO($affectation->getEtablissement()),
            dateAffectation: $affectation->getDateAffectation(),
            dateMutation: $affectation->getDateMutation(),
        );
    }
}
