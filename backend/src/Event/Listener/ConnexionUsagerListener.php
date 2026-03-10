<?php

namespace MonIndemnisationJustice\Event\Listener;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Controller\BrisPorteController as PublicBrisPorteController;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\BrouillonType;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\PersonnePhysique;
use MonIndemnisationJustice\Entity\TestEligibilite;
use MonIndemnisationJustice\Entity\Usager;
use MonIndemnisationJustice\Service\GestionnaireBrouillon;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ConnexionUsagerListener implements EventSubscriberInterface
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly NormalizerInterface $normalizer,
    ) {
    }

    public function onSecurityInteractiveLogin(LoginSuccessEvent $event): void
    {
        $usager = $event->getUser();

        if (!$usager instanceof Usager) {
            return;
        }

        $request = $event->getRequest();

        // S'il existe un test d'éligibilité dans la session du requérant...
        if (null !== ($testEligibilite = $this->getTestEligibiliteEnCours($request, $usager))) {
            // ... associée à aucun de ses dossiers existants...
            if (null !== $testEligibilite && null === $testEligibilite->dossier) {
                // ... alors, on initie un nouveau brouillon de dossier lié à ce test d'éligibilité
                $dossier = Dossier::brisDePorte()
                    ->setUsager($usager)
                    ->setRequerant(
                        $usager->getPersonne()->getPersonnePhysique() ??
                        new PersonnePhysique()
                            ->setPersonne($usager->getPersonne())
                    )
                    ->setBrisPorte(
                        new BrisPorte()
                            ->setRapportAuLogement($testEligibilite->rapportAuLogement)
                            ->setTestEligibilite($testEligibilite)
                    );
                $this->em->persist($dossier);
                $this->em->flush();

                $usager->setNavigation(null);
            }
        } else {
            // Sinon si une déclaration des FDO existe en session...
            $declarationFDO = $this->getDeclarationFDOEnCours($request, $usager);
            // ... associée à aucun de ses dossiers existants...
            if (null !== $declarationFDO && null === $declarationFDO->getDossier()) {
                // ... alors, on crée un nouveau dossier lié à cette déclaration
                /*
                $this->gestionnaireBrouillon->initier(BrouillonType::BROUILLON_DOSSIER_BRIS_PORTE, $usager, donnees: [
                    'idDeclarationFDO' => $declarationFDO->getId(),
                    'dateOperation' => $declarationFDO->getDateOperation(),
                    'adresse' => [
                        'ligne1' => $declarationFDO->getAdresse()->getLigne1(),
                        'ligne2' => $declarationFDO->getAdresse()->getLigne2(),
                        'codePostal' => $declarationFDO->getAdresse()->getCodePostal(),
                        'commune' => $declarationFDO->getAdresse()->getLocalite(),
                    ],
                    'personnePhysique' => $declarationFDO->getCoordonneesRequerant() ? [
                        'personne' => [
                            'civilite' => $declarationFDO->getCoordonneesRequerant()?->getCivilite(),
                            'nom' => $declarationFDO->getCoordonneesRequerant()?->getNom(),
                            'prenom' => $declarationFDO->getCoordonneesRequerant()?->getPrenom(),
                            'courriel' => $declarationFDO->getCoordonneesRequerant()?->getCourriel(),
                            'telephone' => $declarationFDO->getCoordonneesRequerant()?->getTelephone(),
                        ],
                    ] : null,
                ]);
                */
                // TODO créer dossier

                $usager->setNavigation(null);
            }
        }

        // Suppression du contexte de session lié à l'inscription, s'il y en a un
        $request->getSession()->remove(PublicBrisPorteController::CLEF_SESSION_PREINSCRIPTION);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onSecurityInteractiveLogin',
        ];
    }

    protected function getTestEligibiliteEnCours(Request $request, Usager $requerant): ?TestEligibilite
    {
        $preInscription = $request->getSession()->get(PublicBrisPorteController::CLEF_SESSION_PREINSCRIPTION, []);
        $idTestEligibilite = $requerant->getNavigation()?->idTestEligibilite ?? @$preInscription['testEligibilite'] ?? null;

        return $idTestEligibilite ? $this->em->find(TestEligibilite::class, $idTestEligibilite) : null;
    }

    protected function getDeclarationFDOEnCours(Request $request, Usager $requerant): ?DeclarationFDOBrisPorte
    {
        $preInscription = $request->getSession()->get(PublicBrisPorteController::CLEF_SESSION_PREINSCRIPTION, []);
        $idDeclarationFDO = $requerant->getNavigation()?->idDeclaration ?? $preInscription['declarationErreurOperationnelle'] ?? null;

        return $idDeclarationFDO ? $this->em->find(DeclarationFDOBrisPorte::class, $idDeclarationFDO) : null;
    }
}
