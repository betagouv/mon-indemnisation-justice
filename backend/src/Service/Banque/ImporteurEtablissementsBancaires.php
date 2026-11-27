<?php

namespace MonIndemnisationJustice\Service\Banque;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\EtablissementBancaire;

/**
 * Importe en base la liste des établissements bancaires (cf. {@see FichierEtablissementsBancaires}).
 *
 * L'import met à jour ou crée chaque établissement, sans supprimer ceux absents du fichier : des références
 * bancaires peuvent encore y être rattachées. Les logos ne sont pas téléchargés ici : ils sont versionnés dans le
 * dépôt, et seul leur nom de fichier est reporté en base.
 */
class ImporteurEtablissementsBancaires
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly FichierEtablissementsBancaires $fichier,
    ) {
    }

    /**
     * @return int le nombre d'établissements importés
     */
    public function importer(?string $chemin = null): int
    {
        $total = 0;

        foreach ($this->fichier->lire($chemin) as $ligne) {
            $etablissement = $this->em->find(EtablissementBancaire::class, $ligne['code_banque'])
                ?? new EtablissementBancaire()->setCodeBanque($ligne['code_banque']);

            $etablissement
                ->setNom($ligne['nom'])
                ->setLogo($ligne['logo'])
                ->setEstActif(true);

            $this->em->persist($etablissement);
            ++$total;
        }

        $this->em->flush();

        return $total;
    }
}
