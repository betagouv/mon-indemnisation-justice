<?php

namespace MonIndemnisationJustice\Service;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\BrisPorte;
use Twig\Environment;

class DocumentManager
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ImprimanteCourrier $imprimanteCourrier,
        protected readonly Environment $twig,
    ) {}

    public function genererArretePaiement(BrisPorte $dossier): void
    {
        $arretePaiement = $dossier->getOrCreateArretePaiement()->setCorps(
            $this->twig->render('courrier/_corps_arretePaiement.html.twig', [
                'dossier' => $dossier,
            ])
        )->ajouterAuDossier($dossier);

        $arretePaiement = $this->imprimanteCourrier->imprimerDocument($arretePaiement)
            ->setOriginalFilename("Arrêté de paiement - dossier {$dossier->getReference()}")
        ;

        $this->em->persist($arretePaiement);
        $this->em->flush();
    }
}
