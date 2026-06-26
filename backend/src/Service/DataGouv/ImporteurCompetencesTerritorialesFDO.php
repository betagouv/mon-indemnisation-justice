<?php

namespace MonIndemnisationJustice\Service\DataGouv;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Administration;
use MonIndemnisationJustice\Entity\AdministrationType;
use MonIndemnisationJustice\Entity\FDO\EtablissementFDO;
use MonIndemnisationJustice\Entity\GeoCodePostal;
use Psr\Log\LoggerInterface;

/**
 * Importe les données de zone de compétence territoriale gendarmerie et police nationales https://www.data.gouv.fr/fr/datasets/competence-territoriale-gendarmerie-et-police-nationales/.
 */
class ImporteurCompetencesTerritorialesFDO extends AbstractImporteurDataGouv
{
    private const RESSOURCE_COMPETENCE_TERRITORIALE = 'c53cd4d4-4623-4772-9b8c-bc72a9cdf4c2';

    public function __construct(
        protected readonly LoggerInterface $logger,
        protected readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function getResource(): string
    {
        return self::RESSOURCE_COMPETENCE_TERRITORIALE;
    }

    /**
     * @param array{
     *     code_commune: string,
     *     libelle_commune: string,
     *     institution: string,
     *     id_service: string,
     *     service: string,
     *     code_postal: string,
     *     code_postaux?: string,
     *
     * } $entree
     */
    protected function traiterEntree(array $entree): bool
    {
        $typeAdministration = AdministrationType::tryFrom($entree['institution']);
        $administration = $typeAdministration ? $this->em->find(Administration::class, $typeAdministration) : null;

        if ($typeAdministration && $administration) {


            $etablissement = new EtablissementFDO()
                ->setAdministration($administration)
                ->setCodePostal(
                    $this->em->getRepository(GeoCodePostal::class)->findOneBy(
                        [
                            'codePostal' => $entree['code_postal'],
                        ]
                    )
                )
                ->setIdentifiant($entree['id_service'])
                ->setNom($entree['service']);

            /*
            if (isset($entree['codes_postaux'])) {
                $etablissement->setCompetences(
                    $this->em->getRepository(GeoCodePostal::class)->findBy(
                        [
                            'codePostal' => explode('-', $entree['codes_postaux']),
                        ]
                    )
                );
            }
            */

            $this->em->persist($etablissement);
        }

        return true;
    }

    protected function apresImport(): void
    {
        $this->em->flush();
    }
}
