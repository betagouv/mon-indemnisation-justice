<?php

namespace MonIndemnisationJustice\Service\Banque;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\ReferenceBancaire;
use MonIndemnisationJustice\Service\ImprimanteCourrier;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Génère le relevé d'identité bancaire (PDF, sur le modèle de ceux des banques) d'une {@see ReferenceBancaire}, à
 * transmettre au bureau du budget à la place du RIB téléversé par le requérant.
 */
class GenerateurReleveIdentiteBancaire
{
    public function __construct(
        protected readonly ImprimanteCourrier $imprimanteCourrier,
        protected readonly EntityManagerInterface $em,
        #[Autowire('%kernel.project_dir%/public')]
        protected readonly string $dossierPublic,
    ) {
    }

    /**
     * Variables du gabarit `courrier/referenceBancaire.html.twig`.
     */
    public function variablesGabarit(ReferenceBancaire $referenceBancaire, ?Dossier $dossier = null): array
    {
        return [
            'dossier' => $dossier,
            'referenceBancaire' => $referenceBancaire,
            'champsRib' => $referenceBancaire->getChampsRib(),
            'iban' => $referenceBancaire->getIbanComplet(),
            'logo' => $this->logoEnDataUri($referenceBancaire),
        ];
    }

    /**
     * (Re)génère le relevé d'identité bancaire de la référence, qui conserve un unique document mis à jour.
     */
    public function generer(ReferenceBancaire $referenceBancaire, ?Dossier $dossier = null): Document
    {
        $document = $referenceBancaire->getDocument()
            ?? new Document()
                ->setType(DocumentType::TYPE_REFERENCE_BANCAIRE)
                ->setAjoutRequerant(false);

        $document = $this->imprimanteCourrier->imprimerDocument(
            $document,
            variables: $this->variablesGabarit($referenceBancaire, $dossier)
        )->setOriginalFilename(DocumentType::TYPE_REFERENCE_BANCAIRE->nommerFichier($dossier));

        $referenceBancaire->setDocument($document);
        $this->em->persist($document);
        $this->em->persist($referenceBancaire);
        $this->em->flush();

        return $document;
    }

    /**
     * Le logo de l'établissement reconnu, intégré au document en `data:` URI : le document est imprimé depuis une page
     * statique, sans accès aux fichiers publics. Affiché via `<img>`, le SVG n'exécute aucun script.
     */
    protected function logoEnDataUri(ReferenceBancaire $referenceBancaire): ?string
    {
        $logo = $referenceBancaire->getEtablissement()?->getLogo();
        $chemin = null !== $logo ? $this->dossierPublic.TelechargeurLogosEtablissementsBancaires::DOSSIER_PUBLIC.'/'.basename($logo) : null;

        if (null === $chemin || !is_file($chemin)) {
            return null;
        }

        return 'data:image/svg+xml;base64,'.base64_encode(file_get_contents($chemin));
    }
}
