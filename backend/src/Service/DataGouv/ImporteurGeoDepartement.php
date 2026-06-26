<?php

namespace MonIndemnisationJustice\Service\DataGouv;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\GeoDepartement;
use MonIndemnisationJustice\Entity\GeoRegion;

class ImporteurGeoDepartement extends AbstractImporteurDataGouv
{
    // Liste des départements français métropolitains, d'outre mer et les COM ainsi que leurs préfectures https://www.data.gouv.fr/fr/datasets/liste-des-departements-francais-metropolitains-doutre-mer-et-les-com-ainsi-que-leurs-prefectures/
    private const RESOURCE_GEO_DEPARTEMENT = '8603852d-9ae4-4a32-b65f-d5800106e985';

    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    public function getResource(): string
    {
        return self::RESOURCE_GEO_DEPARTEMENT;
    }

    /**
     * @param array{
     *     code: string,
     *     type: string,
     *     nom: string,
     *     chefLieu: string,
     *     region: string,
     *     debutValidite: string,
     *     finValidite: string|null,
     * } $entree
     */
    protected function traiterEntree(array $entree): bool
    {
        /* @var GeoDepartement $departement */
        $departement = $this->em->getRepository(GeoDepartement::class)->find($entree['code']);

        if (null === $departement) {
            $region = $this->em->getRepository(GeoRegion::class)->findOneBy(['nom' => $entree['region']]);

            $departement = new GeoDepartement()
                ->setCode($entree['code'])
                ->setDeploye(false)
                ->setNom($entree['nom'])
                ->setRegion($region);
        }

        $this->em->persist($departement);

        return true;
    }

    protected function apresImport(): void
    {
        $this->em->flush();
    }
}
