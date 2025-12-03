<?php

namespace MonIndemnisationJustice\Event\Listener;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Controller\BrisPorteController as PublicBrisPorteController;
use MonIndemnisationJustice\Entity\Adresse;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\DeclarationErreurOperationnelle;
use MonIndemnisationJustice\Entity\Requerant;
use MonIndemnisationJustice\Entity\TestEligibilite;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class ConnexionRequerantListener implements EventSubscriberInterface
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {}

    public function onSecurityInteractiveLogin(LoginSuccessEvent $event): void
    {
        $requerant = $event->getUser();

        if (!$requerant instanceof Requerant) {
            return;
        }

        $request = $event->getRequest();

        // S'il existe un test d'éligibilité dans la session du requérant...
        if (null !== ($testEligibilite = $this->getTestEligibiliteEnCours($request, $requerant))) {
            // ... associée à aucun de ses dossiers existants...
            if (null !== $testEligibilite && null === $testEligibilite->dossier) {
                // ... alors, on crée un nouveau dossier lié à ce test d'éligibilité
                $dossier = (new BrisPorte())
                    ->setRequerant($requerant)
                    ->setQualiteRequerant($testEligibilite->rapportAuLogement)
                    ->setTestEligibilite($testEligibilite)
                ;
                $requerant->setNavigation(null);

                $this->em->persist($requerant);
                $this->em->persist($dossier);
                $this->em->flush();
            }
        } else {
            // Sinon si une déclaration des FDO existe en session...
            $declarationFDO = $this->getDeclarationFDOEnCours($request, $requerant);
            // ... associée à aucun de ses dossiers existants...
            if (null !== $declarationFDO && null === $declarationFDO->getDossier()) {
                // ... alors, on crée un nouveau dossier lié à cette déclaration
                $dossier = (new BrisPorte())
                    ->setRequerant($requerant)
                    ->setDeclarationFDO($declarationFDO)
                    ->setDateOperationPJ($declarationFDO->getDateOperation())
                    // On recrée une nouvelle adresse pour conserver les données des FDO et pouvoir plus tard comparer et arbitrer
                    ->setAdresse(
                        (new Adresse())
                            ->setLigne1($declarationFDO->getAdresse()->getLigne1())
                            ->setLigne2($declarationFDO->getAdresse()->getLigne2())
                            ->setCodePostal($declarationFDO->getAdresse()->getCodePostal())
                            ->setLocalite($declarationFDO->getAdresse()->getLocalite())
                    )
                ;

                $requerant->setNavigation(null);

                $this->em->persist($requerant);
                $this->em->persist($dossier);
                $this->em->flush();
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

    protected function getTestEligibiliteEnCours(Request $request, Requerant $requerant): ?TestEligibilite
    {
        $preInscription = $request->getSession()->get(PublicBrisPorteController::CLEF_SESSION_PREINSCRIPTION, []);
        $idTestEligibilite = $requerant->getNavigation()?->idTestEligibilite ?? @$preInscription['testEligibilite'] ?? null;

        return $idTestEligibilite ? $this->em->find(TestEligibilite::class, $idTestEligibilite) : null;
    }

    protected function getDeclarationFDOEnCours(Request $request, Requerant $requerant): ?DeclarationErreurOperationnelle
    {
        $preInscription = $request->getSession()->get(PublicBrisPorteController::CLEF_SESSION_PREINSCRIPTION, []);
        $idDeclarationFDO = $requerant->getNavigation()?->idDeclaration ?? $preInscription['declarationErreurOperationnelle'] ?? null;

        return $idDeclarationFDO ? $this->em->find(DeclarationErreurOperationnelle::class, $idDeclarationFDO) : null;
    }
}
