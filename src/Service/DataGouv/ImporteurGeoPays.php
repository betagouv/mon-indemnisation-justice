<?php

namespace MonIndemnisationJustice\Service\DataGouv;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\GeoPays;

class ImporteurGeoPays implements DataGouvProcessor
{
    // Une valeur particulière est attribuée à la France côté FranceConnect https://docs.partenaires.franceconnect.gouv.fr/fs/fs-technique/fs-technique-scope-fc/#liste-des-claims
    // mais pas renseignée sur la source data.gouv.fr
    public const CODE_INSEE_FRANCE = '99100';

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
            ->setCodeInsee('FRA' === $record['ISO_alpha3T'] ? self::CODE_INSEE_FRANCE : $record['CODE_COG']);

        $this->entityManager->persist($pays);
        $this->entityManager->flush();
    }

    public function onProcessed(): void
    {
        // TODO: Implement onProcessed() method.
    }
}
