<?php

namespace MonIndemnisationJustice\Tests\Service\Banque;

use MonIndemnisationJustice\Entity\EtablissementBancaire;
use Doctrine\ORM\EntityRepository;
use MonIndemnisationJustice\Service\Banque\ImporteurEtablissementsBancaires;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(ImporteurEtablissementsBancaires::class)]
class ImporteurEtablissementsBancairesTest extends KernelTestCase
{
    private const FICHIER = __DIR__.'/../../ressources/banque/etablissements-bancaires.csv';

    protected ImporteurEtablissementsBancaires $importeur;
    /** @var EntityRepository<EtablissementBancaire> */
    protected EntityRepository $repository;

    public function setUp(): void
    {
        self::bootKernel();

        $this->importeur = static::getContainer()->get(ImporteurEtablissementsBancaires::class);
        $this->repository = static::getContainer()->get('doctrine.orm.entity_manager')->getRepository(EtablissementBancaire::class);
    }

    public function testImportIgnoreLesCodesInvalides(): void
    {
        $this->assertSame(2, $this->importeur->importer(self::FICHIER));

        $etablissement = $this->repository->find('99902');
        $this->assertInstanceOf(EtablissementBancaire::class, $etablissement);
        $this->assertSame('Banque, avec virgule', $etablissement->getNom());
        $this->assertSame('0123456789abcdef.svg', $etablissement->getLogo());
        $this->assertNull($this->repository->find('99901')->getLogo());
    }

    public function testImportIdempotentEtMiseAJour(): void
    {
        $this->importeur->importer(self::FICHIER);
        $total = $this->repository->count([]);

        // Un établissement modifié entre 2 imports est remis à jour, sans doublon
        $this->repository->find('99901')->setNom('Ancien nom');
        static::getContainer()->get('doctrine.orm.entity_manager')->flush();

        $this->importeur->importer(self::FICHIER);

        $this->assertSame($total, $this->repository->count([]));
        $this->assertSame('Banque de test', $this->repository->find('99901')->getNom());
    }
}
