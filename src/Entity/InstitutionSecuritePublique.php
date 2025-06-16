<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'institutions_securite_publique')]
class InstitutionSecuritePublique
{
    #[ORM\Column(name: 'type', length: 2, nullable: true, enumType: TypeInstitutionSecuritePublique::class)]
    #[ORM\Id]
    protected TypeInstitutionSecuritePublique $type;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $dateIntegration = null;

    public function getType(): TypeInstitutionSecuritePublique
    {
        return $this->type;
    }
}
