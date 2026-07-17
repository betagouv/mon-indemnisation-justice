<?php

declare(strict_types=1);

namespace Entity;

use MonIndemnisationJustice\Entity\PersonneMorale;
use MonIndemnisationJustice\Entity\PersonneMoraleType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PersonneMoraleTest extends TestCase
{
    public static function donneesTestLibelleDe(): array
    {
        return [
            'societe' => [new PersonneMorale()->setType(PersonneMoraleType::ENTREPRISE_PRIVEE)->setRaisonSociale('Grande Entreprise'), 'de la société Grande Entreprise'],
            // Cas d'une SCI déclarée en tant que société (doit être nettoyée, i.e. convertie)
            'societe_pour_sci' => [new PersonneMorale()->setType(PersonneMoraleType::ENTREPRISE_PRIVEE)->setRaisonSociale('SCI Esse Cého'), 'de la SCI Esse Cého'],
            // SCI pour laquele le terme SCI est repris dans la raison sociale
            'sci_redondante' => [new PersonneMorale()->setType(PersonneMoraleType::SCI)->setRaisonSociale('SCI Esse Cého'), 'de la SCI Esse Cého'],
            'sci' => [new PersonneMorale()->setType(PersonneMoraleType::SCI)->setRaisonSociale('Esse Cého'), 'de la SCI Esse Cého'],
            'association' => [new PersonneMorale()->setType(PersonneMoraleType::ASSOCIATION)->setRaisonSociale('Demal Faiteur'), "de l'association Demal Faiteur"],
            'syndic' => [new PersonneMorale()->setType(PersonneMoraleType::SYNDIC)->setRaisonSociale('Adini Siative'), 'du syndic Adini Siative'],
        ];
    }

    #[DataProvider('donneesTestLibelleDe')]
    public function testLibelleDe(PersonneMorale $personneMorale, string $libelleAttendu): void
    {
        $this->assertEquals($libelleAttendu, $personneMorale->getLibelleDe());
    }
}
