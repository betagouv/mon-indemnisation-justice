<?php

namespace MonIndemnisationJustice\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use MonIndemnisationJustice\Service\Banque\ImporteurEtablissementsBancaires;

/**
 * Charge le référentiel versionné complet des établissements bancaires (avec leurs logos), comme en production.
 */
class EtablissementBancaireFixture extends Fixture
{
    public function __construct(
        protected readonly ImporteurEtablissementsBancaires $importeur,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->importeur->importer();
    }
}
