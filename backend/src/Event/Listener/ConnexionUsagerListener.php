<?php

namespace MonIndemnisationJustice\Event\Listener;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Controller\BrisPorteController as PublicBrisPorteController;
use MonIndemnisationJustice\Entity\Adresse;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\PersonnePhysique;
use MonIndemnisationJustice\Entity\TestEligibilite;
use MonIndemnisationJustice\Entity\Usager;
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
            $dossier = $declarationFDO ? $this->em->getRepository(Dossier::class)->findOneBy(['declarationFDO' => $declarationFDO]) : null;

            // ... associée à aucun de ses dossiers existants...
            if (null !== $declarationFDO && null === $dossier) {
                // ... alors, on crée un nouveau dossier lié à cette déclaration
                $dossier = Dossier::brisDePorte()
                    ->setUsager($usager)
                    ->setRequerant(
                        $usager->getPersonne()->getPersonnePhysique() ??
                        new PersonnePhysique()
                            ->setPersonne($usager->getPersonne())
                    )
                    ->setBrisPorte(
                        new BrisPorte()
                            ->setTestEligibilite($testEligibilite)
                            ->setAdresse(
                                new Adresse()
                                    // On recopie les valeurs de l'adresse de la déclaration pour permettre au requérant de modifier
                                    ->setLigne1($declarationFDO->getAdresse()->getLigne1())
                                    ->setLigne2($declarationFDO->getAdresse()->getLigne2())
                                    ->setCodePostal($declarationFDO->getAdresse()->getCodePostal())
                                    ->setLocalite($declarationFDO->getAdresse()->getLocalite())
                            )
                    );

                $this->em->persist($dossier);
                $this->em->flush();

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
