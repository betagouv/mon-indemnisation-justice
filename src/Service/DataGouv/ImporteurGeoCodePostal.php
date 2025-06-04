<?php

namespace MonIndemnisationJustice\Service\DataGouv;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\GeoCodePostal;
use MonIndemnisationJustice\Entity\GeoCommune;
use MonIndemnisationJustice\Entity\GeoDepartement;

class ImporteurGeoCodePostal implements DataGouvProcessor
{
    //  Base officielle des codes postaux https://www.data.gouv.fr/fr/datasets/base-officielle-des-codes-postaux/
    private const RESOURCE_GEO_CODE_POSTAL = 'ca429285-b5a1-442b-8edf-8e6f68c304c9';

    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getResource(): string
    {
        return self::RESOURCE_GEO_CODE_POSTAL;
    }

    public function processRecord(array $record): void
    {
        $codeInsee = $record['#Code_commune_INSEE'];
        $codePostal = $record['Code_postal'];

        /** @var GeoCodePostal $code */
        $code = $this->entityManager->getRepository(GeoCodePostal::class)->findOneBy([
            'commune' => $codeInsee,
            'codePostal' => $codePostal,
        ]);

        if (null === $code) {
            $commune = $this->entityManager->getRepository(GeoCommune::class)->find($codeInsee);

            if (null === $commune) {
                $codeDepartement = substr($record['#Code_commune_INSEE'], 0, str_starts_with($record['#Code_commune_INSEE'], '97') ? 3 : 2);
                $departement = $this->entityManager->getRepository(GeoDepartement::class)->find($codeDepartement);

                $commune = (new GeoCommune())
                ->setCode($codeInsee)
                ->setNom($this->normaliserNom($record['Nom_de_la_commune']))
                ->setDepartement($departement);

                $this->entityManager->persist($commune);
            }

            $code = (new GeoCodePostal())->setCodePostal($codePostal)->setCommune($commune);

            $this->entityManager->persist($code);
            $this->entityManager->flush();
        }
    }

    public function onProcessed(): void
    {
        // TODO: Implement onProcessed() method.
    }

    /**
     * "L ABERGEMENT CLEMENCIAT" => "L'Abergement-Clemenciat".
     * "MARSEILLE 16" => "Marseille"
     * "PONT L EVEQUE" => "Pont-l'eveque".
     */
    public function normaliserNom(string $nom): string
    {
        return array_reduce(
            explode(' ', $nom),
            function (string $nom, string $element) {
                // On élimine les valeurs numériques type numéro d'arrondissement
                if (is_numeric($element)) {
                    return $nom;
                }

                if ('L' === strtoupper($element)) {
                    return empty($nom) ? "L'" : "$nom-l'";
                }

                return "$nom".(str_ends_with($nom, "'") || empty($nom) ? '' : '-').ucfirst(strtolower($element));
            },
            ''
        );
    }
}
