<?php

namespace MonIndemnisationJustice\Event\Listener;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Requerant\Brouillon\Dto\DossierDto;
use MonIndemnisationJustice\Api\Requerant\Brouillon\Dto\UsagerDto;
use MonIndemnisationJustice\Controller\BrisPorteController as PublicBrisPorteController;
use MonIndemnisationJustice\Entity\BrouillonType;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\Requerant;
use MonIndemnisationJustice\Entity\TestEligibilite;
use MonIndemnisationJustice\Service\GestionnaireBrouillon;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ConnexionRequerantListener implements EventSubscriberInterface
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GestionnaireBrouillon $gestionnaireBrouillon,
        protected readonly NormalizerInterface $normalizer,
    ) {
    }

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
                // ... alors, on initie un nouveau brouillon de dossier lié à ce test d'éligibilité
                $dossier = new DossierDto();
                $dossier->requerant = new UsagerDto();
                $dossier->requerant->estPersonneMorale = $requerant->getIsPersonneMorale();
                $dossier->requerant->raisonSociale = $requerant->getPersonneMorale()?->getRaisonSociale();
                $dossier->requerant->siren = $requerant->getPersonneMorale()?->getSirenSiret();
                // TODO finir de transvaser les champs
                $dossier->testEligibilite = new TestEligibilite();
                $dossier->testEligibilite->id = $testEligibilite->id;

                $this->gestionnaireBrouillon->initierDepuis($dossier, $requerant);

                $requerant->setNavigation(null);
            }
        } else {
            // Sinon si une déclaration des FDO existe en session...
            $declarationFDO = $this->getDeclarationFDOEnCours($request, $requerant);
            $dossier = $declarationFDO ? $this->em->getRepository(BrisPorte::class)->findOneBy(['declarationFDO' => $declarationFDO]) : null;

            // ... associée à aucun de ses dossiers existants...
            if (null !== $declarationFDO && null === $dossier) {
                // ... alors, on crée un nouveau dossier lié à cette déclaration
                /*
                $dossier = new BrisPorte()
                    ->setRequerant($requerant)
                    ->setDeclarationFDO($declarationFDO)
                    ->setDateOperationPJ($declarationFDO->getDateOperation())
                    // On recrée une nouvelle adresse pour conserver les données des FDO et pouvoir plus tard comparer et arbitrer
                    ->setAdresse(
                        new Adresse()
                            ->setLigne1($declarationFDO->getAdresse()->getLigne1())
                            ->setLigne2($declarationFDO->getAdresse()->getLigne2())
                            ->setCodePostal($declarationFDO->getAdresse()->getCodePostal())
                            ->setLocalite($declarationFDO->getAdresse()->getLocalite())
                    );
                */

                $this->gestionnaireBrouillon->initier(BrouillonType::BROUILLON_DOSSIER_BRIS_PORTE, requerant: $requerant, donnees: [
                    'requerant' => [
                        'estPersonneMorale' => $requerant->getIsPersonneMorale(),
                        'raisonSociale' => $requerant->getPersonneMorale()?->getRaisonSociale(),
                        'siren' => $requerant->getPersonneMorale()?->getSirenSiret(),

                        // TODO finir de transvaser les champs
                    ],
                ]);

                $requerant->setNavigation(null);
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

    protected function getDeclarationFDOEnCours(Request $request, Requerant $requerant): ?DeclarationFDOBrisPorte
    {
        $preInscription = $request->getSession()->get(PublicBrisPorteController::CLEF_SESSION_PREINSCRIPTION, []);
        $idDeclarationFDO = $requerant->getNavigation()?->idDeclaration ?? $preInscription['declarationErreurOperationnelle'] ?? null;

        return $idDeclarationFDO ? $this->em->find(DeclarationFDOBrisPorte::class, $idDeclarationFDO) : null;
    }
}
