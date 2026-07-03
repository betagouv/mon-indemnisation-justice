<?php

namespace MonIndemnisationJustice\Service\DataGouv;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\GeoPays;

class ImporteurGeoPays extends AbstractImporteurDataGouv
{
    // Référentiel des pays et des territoires https://www.data.gouv.fr/fr/datasets/referentiel-des-pays-et-des-territoires/#/resources/2b38f28d-15e7-4f0c-b61d-6ca1d9b1cfa2
    private const RESOURCE_GEO_PAYS = '2b38f28d-15e7-4f0c-b61d-6ca1d9b1cfa2';

    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    public function getResource(): string
    {
        return self::RESOURCE_GEO_PAYS;
    }

    public function traiterEntree(array $entree): bool
    {
        if (3 === strlen($entree['ISO_alpha3'])) {
            /** @var GeoPays $pays */
            $pays = $this->em->getRepository(GeoPays::class)->find($entree['ISO_alpha3']) ?? (new GeoPays())->setCode($entree['ISO_alpha3']);

            $pays
                ->setNom($entree['NOM_COURT'])
                ->setCodeInsee('FRA' === $entree['ISO_alpha3'] ? GeoPays::CODE_INSEE_FRANCE : $entree['CODE_COG']);

            $this->em->persist($pays);

            return true;
        }

        return false;
    }

    protected function apresImport(): void
    {
        $this->em->flush();
    }
}
