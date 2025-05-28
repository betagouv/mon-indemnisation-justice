<?php

namespace MonIndemnisationJustice\Service\DataGouv;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\GeoPays;

class ImporteurGeoPays implements DataGouvProcessor
{
    // Référentiel des pays et des territoires https://www.data.gouv.fr/fr/datasets/referentiel-des-pays-et-des-territoires/#/resources
    private const RESOURCE_GEO_PAYS = '2b38f28d-15e7-4f0c-b61d-6ca1d9b1cfa2';

    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getResource(): string
    {
        return self::RESOURCE_GEO_PAYS;
    }

    public function processRecord(array $record): void
    {
        /** @var GeoPays $pays */
        $pays = $this->entityManager->getRepository(GeoPays::class)->find($record['ISO_alpha3']) ?? (new GeoPays())->setCode($record['ISO_alpha3']);

        $pays
            ->setNom($record['NOM_COURT'])
            ->setCodeInsee($record['CODE_COG']);

        $this->entityManager->persist($pays);
        $this->entityManager->flush();
    }

    public function onProcessed(): void
    {
        // TODO: Implement onProcessed() method.
    }
}
