<?php

namespace MonIndemnisationJustice\Service\Signature;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\DossierType;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Entity\RequeteSignatureRequerant;
use MonIndemnisationJustice\Service\DocumentManager;
use MonIndemnisationJustice\Service\DossierManager;
use MonIndemnisationJustice\Service\Signature\Evenement\EvenementSignatureType;
use MonIndemnisationJustice\Service\Signature\Prestataire\Requerant\PrestataireSignatureRequerantInterface;
use Psr\Log\LoggerInterface;

class GestionnaireSignature
{
    public function __construct(
        protected readonly DocumentManager $documentManager,
        protected readonly DossierManager $dossierManager,
        protected readonly PrestataireSignatureRequerantInterface $prestataireSignatureRequerant,
        protected readonly EntityManagerInterface $em,
        protected readonly LoggerInterface $logger,
    ) {

    }

    public function estEligibleSignatureRequerant(Dossier $dossier): bool
    {
        return DossierType::BRIS_PORTE === $dossier->getType() && !$dossier->estPersonneMorale();
    }

    public function initierSignatureRequerant(Dossier $dossier): ?RequeteSignatureRequerant
    {
        if (EtatDossierType::DOSSIER_OK_A_APPROUVER !== $dossier->getEtatDossier()->getEtat()) {
            throw new \Exception("Ce dossier n'est pas à accepter par le requérant");
        }

        if (!$this->estEligibleSignatureRequerant($dossier)) {
            return null;
        }

        if (!$dossier->peutDemarrerSignature()) {
            throw new \Exception('Une signature est déjà en cours pour ce dossier');
        }

        $requeteSignature = new RequeteSignatureRequerant()
            ->setIdentifiantExterieur('');
        $dossier->ajouterRequeteSignatureRequerant($requeteSignature);

        $this->em->persist($requeteSignature);
        $this->em->flush();

        return $requeteSignature;
    }

    /**
     * @throws \Exception
     */
    public function activerSignatureRequerant(RequeteSignatureRequerant $requeteSignature, string $rib): ?RequeteSignatureRequerant
    {
        $requeteSignature = $this->prestataireSignatureRequerant->activerRequeteSignature(
            $requeteSignature,
            $this->genererDeclarationAcceptation(
                $requeteSignature->getDossier(),
                $rib
            )
        );


        $this->em->persist($requeteSignature);
        $this->em->flush();

        return $requeteSignature;
    }

    public function genererDeclarationAcceptation(Dossier $dossier, string $rib): DocumentASigner
    {
        $declarationAcceptation = $this->documentManager
            ->generer(
                $dossier,
                DocumentType::TYPE_FORMULAIRE_ACCEPTATION,
                montantIndemnisation: $dossier->getMontantIndemnisation(),
                contexte: [
                    'signature' => [
                        'lieu' => $dossier->getRequerantPersonnePhysique()?->getAdresse()?->getLocalite() ?? $dossier->getRequerantPersonneMorale()?->getAdresse()?->getLocalite() ?? $dossier->getBrisPorte()->getAdresse()->getLocalite() ?? '',
                        'rib' => $rib,
                    ],
                ],
                extraireObjets: true
            );

        $objets = $declarationAcceptation->getMetaDonnee('objets');
        $emplacementSignature = null;

        if (is_array($objets) && isset($objets['visuel-signature'])) {
            $emplacementSignature = new EmplacementSignature(
                x: $objets['visuel-signature']['x'],
                y: $objets['visuel-signature']['y'],
                largeur: $objets['visuel-signature']['width'],
                hauteur: $objets['visuel-signature']['height'],
                numeroPage: $objets['visuel-signature']['page'],
                hauteurPage: (int) round($objets['visuel-signature']['pageHeight'] ?? EmplacementSignature::HAUTEUR_PAGE_A4),
            );
        }


        $contenu = $this->documentManager->getContenuTexte($declarationAcceptation);

        return new DocumentASigner(
            $declarationAcceptation->getFilename(),
            $contenu,
            $emplacementSignature
        );
    }

    public function synchroniserEvenement(array $donnees): ?RequeteSignatureRequerant
    {
        $evenementSignature = $this->prestataireSignatureRequerant->extraireEvenementSignatureWebhook($donnees);

        if (null !== $evenementSignature) {
            $requeteSignature = $evenementSignature->requeteSignatureRequerant;
            $dossier = $requeteSignature->getDossier();

            // Signature expirée chez le prestataire : on la date de l'évènement, sauf si elle a été signée entre-temps.
            // La livraison étant « au moins une fois », une requête déjà expirée n'est pas modifiée
            if (EvenementSignatureType::EXPIREE === $evenementSignature->type) {
                if (!$requeteSignature->estSignee() && !$requeteSignature->estExpiree()) {
                    $requeteSignature->setDateExpiration($evenementSignature->date);
                    $this->em->persist($requeteSignature);
                    $this->em->flush();
                }

                return $requeteSignature;
            }

            // TODO vérifier si pas déjà signée
            if (EvenementSignatureType::SIGNEE === $evenementSignature->type) {
                $requeteSignature->setSignee();
                $type = DocumentType::TYPE_COURRIER_REQUERANT;

                $this->documentManager->ajouterDocument(
                    $dossier,
                    $dossier->getOrCreateDocument($type)
                        ->setOriginalFilename($type->nommerFichier($dossier))
                        ->setType($type)
                        ->setMime('application/pdf')
                        ->setAjoutRequerant(true),
                    $this->prestataireSignatureRequerant->telechargerDocumentSigne($requeteSignature),
                    'pdf',
                );

                $requeteSignature->setSignee();
                $this->em->persist($requeteSignature);

                $dossier = $this->dossierManager->avancer($dossier);
                $this->em->persist($dossier);
                $this->em->flush();

                return $requeteSignature;
            }
            // TODO gérer en erreur
        }

        return null;
    }
}
