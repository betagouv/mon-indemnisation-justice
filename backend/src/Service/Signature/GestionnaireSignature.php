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

    /**
     * @throws \Exception
     */
    public function initierSignatureRequerant(Dossier $dossier): ?RequeteSignatureRequerant
    {
        if (EtatDossierType::DOSSIER_OK_A_APPROUVER !== $dossier->getEtatDossier()->getEtat()) {
            throw new \Exception("Ce dossier n'est pas à accepter par le requérant");
        }

        if ($this->estEligibleSignatureRequerant($dossier)) {
            $requeteSignature = $this->prestataireSignatureRequerant->creerRequeteSignature($dossier, $this->genererDeclarationAcceptation(
                $dossier,
            ));

            $this->em->persist($requeteSignature);
            $this->em->flush();

            return $requeteSignature;
        }

        return null;
    }

    public function genererDeclarationAcceptation(Dossier $dossier): DocumentASigner
    {
        $declarationAcceptation = $this->documentManager
            ->generer(
                $dossier,
                DocumentType::TYPE_FORMULAIRE_ACCEPTATION,
                montantIndemnisation: $dossier->getMontantIndemnisation(),
                contexte: [
                    'signature' => [
                        'date' => new \DateTimeImmutable(),
                        'lieu' => $dossier->getRequerantPersonnePhysique()?->getAdresse()?->getLocalite() ?? $dossier->getRequerantPersonneMorale()?->getAdresse()?->getLocalite() ?? $dossier->getBrisPorte()->getAdresse()->getLocalite() ?? '',
                        'rib' => [
                            'type' => 'personnel',
                        ],
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
            );
        } else {

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
        $requeteSignature = $evenementSignature->requeteSignatureRequerant;
        $dossier = $requeteSignature->getDossier();

        if (null !== $evenementSignature) {
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
            // TODO gérer expirée
        }

        return null;
    }
}
