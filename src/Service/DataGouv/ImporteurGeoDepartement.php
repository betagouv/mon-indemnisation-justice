<?php

namespace MonIndemnisationJustice\Service\DataGouv;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\GeoDepartement;
use MonIndemnisationJustice\Entity\GeoRegion;

class ImporteurGeoDepartement implements DataGouvProcessor
{
    // Liste des départements français métropolitains, d'outre mer et les COM ainsi que leurs préfectures https://www.data.gouv.fr/fr/datasets/liste-des-departements-francais-metropolitains-doutre-mer-et-les-com-ainsi-que-leurs-prefectures/
    public const RESOURCE_GEO_DEPARTEMENT = '8603852d-9ae4-4a32-b65f-d5800106e985';

    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getResource(): string
    {
        return self::RESOURCE_GEO_DEPARTEMENT;
    }

    public function processRecord(array $record): void
    {
        if (in_array($record['type'], ['DPT', 'DOM'])) {
            /* @var GeoDepartement $departement */
            $departement = $this->entityManager->getRepository(GeoDepartement::class)->find($record['code']);

            if (null === $departement) {
                $region = $this->entityManager->getRepository(GeoRegion::class)->findOneBy(['nom' => $record['region']]);

                $departement = (new GeoDepartement())
                    ->setCode($record['code'])
                    ->setDeploye(false)
                    ->setNom($record['nom'])
                    ->setRegion($region)
                ;
            }

            $this->entityManager->persist($departement);
            $this->entityManager->flush();
        }
    }

    public function onProcessed(): void
    {
        // TODO: Implement onProcessed() method.
    }
}
