<?php

namespace MonIndemnisationJustice\Service\DataGouv;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Avocat;
use MonIndemnisationJustice\Entity\Barreau;
use MonIndemnisationJustice\Entity\Civilite;

class ImporteurAvocat extends AbstractImporteurDataGouv
{
    // Annuaire des avocats de France (CNB) https://www.data.gouv.fr/datasets/annuaire-des-avocats-de-france
    private const RESOURCE_AVOCATS = '99c8ff76-c0ff-4046-80d7-c10fc8e51c34';

    private const TAILLE_PAGE = 200;
   
    private const TAILLE_LOT = 200;

    /** @var array<string, Barreau> */
    private array $barreauxCache = [];

    private int $compteurLot = 0;

    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    public function getResource(): string
    {
        return self::RESOURCE_AVOCATS;
    }

    /**
     * La ressource dépasse la limite de lignes de l'export CSV de tabular-api.data.gouv.fr (HTTP 403
     * "Output is too long" au-delà de 50 000 lignes, pour ~81 000 avocats) : on pagine donc via l'API
     * JSON plutôt que de passer par le téléchargement CSV de la classe parente.
     *
     * @return \Generator<int, array>
     */
    public function lireRessourceCSV(string $ressource, bool $conserverFichier = false, ?int $limite = null): \Generator
    {
        $page = 1;
        $compteur = 0;

        while (null === $limite || $compteur < $limite) {
            // `page_size` reste toujours à sa valeur maximale, y compris sur la dernière page d'un import limité par
            // `--limite` : le faire varier casserait le calcul d'offset de l'API (offset = (page-1)*page_size), qui
            // suppose une taille de page constante d'une requête à l'autre. La limite est de toute façon appliquée
            // ligne à ligne juste en dessous, donc récupérer quelques lignes de trop sur la dernière page ne pose pas
            // de problème : elles ne seront simplement jamais yield-ées.
            $reponse = $this->client->request('GET', "/api/resources/{$ressource}/data/", [
                'query' => ['page' => $page, 'page_size' => self::TAILLE_PAGE],
            ]);
            $corps = json_decode($reponse->getBody()->getContents(), true);
            $lignes = $corps['data'] ?? [];

            if (empty($lignes)) {
                break;
            }

            foreach ($lignes as $ligne) {
                yield $ligne;
                ++$compteur;
                if (null !== $limite && $compteur >= $limite) {
                    break;
                }
            }

            if (null === ($corps['links']['next'] ?? null)) {
                break;
            }

            ++$page;
        }
    }

    public function traiterEntree(array $entree): bool
    {
        $codeBarreau = trim((string) ($entree['BarreauId'] ?? ''));
        $numeroCnbf = trim((string) ($entree['avCnbfCode'] ?? ''));

        if ('' === $codeBarreau || '' === $numeroCnbf) {
            return false;
        }

        if (!isset($this->barreauxCache[$codeBarreau])) {
            $barreau = $this->em->getRepository(Barreau::class)->find($codeBarreau)
                ?? new Barreau()->setId($codeBarreau);
            $barreau->setNom((string) ($entree['Barreau'] ?? $codeBarreau));
            $this->em->persist($barreau);
            $this->barreauxCache[$codeBarreau] = $barreau;
        }

        $avocat = $this->em->getRepository(Avocat::class)->find($numeroCnbf)
            ?? new Avocat()->setNumeroCnbf($numeroCnbf);

        $avocat
            ->setNom((string) ($entree['avNom'] ?? ''))
            ->setPrenom((string) ($entree['avPrenom'] ?? ''))
            ->setRaisonSociale($entree['cbRaisonSociale'] ?? null)
            ->setEmail($entree['avMelOrdre'] ?? null)
            ->setCivilite(self::civiliteDepuis($entree['civilit'] ?? null))
            ->setTelephone($entree['cbTel'] ?? null)
            ->setBarreau($this->barreauxCache[$codeBarreau]);

        $this->em->persist($avocat);

        if (++$this->compteurLot >= self::TAILLE_LOT) {
            $this->em->flush();
            // clear() détache toutes les entités managées, y compris celles du cache de barreaux : on le vide donc
            // aussi pour forcer un nouveau find() (qui retournera une entité fraîchement managée) au prochain besoin,
            // plutôt que de réutiliser une entité détachée sur un avocat persist()é après ce clear().
            $this->em->clear();
            $this->barreauxCache = [];
            $this->compteurLot = 0;
        }

        return true;
    }

    protected function apresImport(): void
    {
        $this->em->flush();
    }

    // Le CNB code le sexe ("M"/"F"), pas la civilité : on la déduit, faute de mieux, comme le fait déjà le reste
    // de l'application (cf. Civilite::estFeminin()).
    private static function civiliteDepuis(?string $sexe): ?Civilite
    {
        return match ($sexe) {
            'M' => Civilite::M,
            'F' => Civilite::MME,
            default => null,
        };
    }
}
