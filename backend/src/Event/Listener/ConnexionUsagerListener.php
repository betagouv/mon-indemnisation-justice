<?php

namespace MonIndemnisationJustice\Event\Listener;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Public\Dysfonctionnement\SauvegarderTestEligibiliteDysfonctionnementEndpoint;
use MonIndemnisationJustice\Controller\BrisPorteController as PublicBrisPorteController;
use MonIndemnisationJustice\Entity\Adresse;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\PersonnePhysique;
use MonIndemnisationJustice\Entity\TestEligibiliteBrisPorte;
use MonIndemnisationJustice\Entity\TestEligibiliteDysfonctionnement;
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

        // S'il existe un test d'éligibilité dysfonctionnement dans la session du requérant, ou à défaut déjà
        // rattaché à cet usager en base (cf. InscriptionUsagerService::creerUsager) sans dossier associé...
        if (null !== ($testEligibiliteDysfonctionnement = $this->getTestEligibiliteDysfonctionnementEnCours($request, $usager))) {
            // ... associé à aucun de ses dossiers existants...
            if (null === $testEligibiliteDysfonctionnement->dossier) {
                if (null === $testEligibiliteDysfonctionnement->usager) {
                    $testEligibiliteDysfonctionnement->usager = $usager;
                }

                // ... alors, on initie un nouveau brouillon de dossier lié à ce test d'éligibilité
                $dossier = Dossier::dysfonctionnementDepuisTestEligibilite($testEligibiliteDysfonctionnement);
                $this->em->persist($dossier);
                $this->em->flush();
            }

            $request->getSession()->remove(SauvegarderTestEligibiliteDysfonctionnementEndpoint::CLEF_SESSION);

            return;
        }

        // S'il existe un test d'éligibilité dans la session du requérant...
        if (null !== ($testEligibilite = $this->getTestEligibiliteEnCours($request, $usager))) {
            // ... associée à aucun de ses dossiers existants...
            if (null !== $testEligibilite && null === $testEligibilite->dossier) {
                if (null === $testEligibilite->usager) {
                    $testEligibilite->usager = $usager;
                }

                // ... alors, on initie un nouveau brouillon de dossier lié à ce test d'éligibilité
                $dossier = Dossier::brisDePorteDepuisTestEligibilite($testEligibilite);
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
                $dossier = Dossier::brisDePorte()
                    ->setUsager($usager)
                    ->setRequerant(
                        // Si l'usager a déjà une personne physique associée, on utilise celle-ci
                        $usager->getPersonne()->getPersonnePhysique() ??
                        new PersonnePhysique()
                            ->setPersonne($usager->getPersonne())
                    )
                    ->setBrisPorte(
                        new BrisPorte()
                            ->setDateOperation($declarationFDO->getDateOperation())
                            ->setAdresse(
                                new Adresse()
                                    // On recopie les valeurs de l'adresse de la déclaration pour permettre au requérant de modifier
                                    ->setLigne1($declarationFDO->getAdresse()->getLigne1())
                                    ->setLigne2($declarationFDO->getAdresse()->getLigne2())
                                    ->setCodePostal($declarationFDO->getAdresse()->getCodePostal())
                                    ->setLocalite($declarationFDO->getAdresse()->getLocalite())
                            )
                    );
                $declarationFDO->setBrisPorte($dossier->getBrisPorte());
                $this->em->persist($declarationFDO);
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

    /**
     * Cherche d'abord dans la session (chemin habituel, pas de requête supplémentaire), puis à défaut en base
     * un test déjà rattaché à cet usager (cf. InscriptionUsagerService::creerUsager, qui fait ce rattachement
     * dès l'inscription) sans dossier associé — nécessaire car la session prise au moment du test d'éligibilité
     * ne survit pas toujours jusqu'à la connexion finale (lien de vérification ouvert dans un autre contexte,
     * onglet, navigateur, ou pré-visité par un scanner anti-hameçonnage), alors que le lien en base, lui, existe
     * dès l'inscription et ne dépend d'aucun état de session.
     */
    protected function getTestEligibiliteDysfonctionnementEnCours(Request $request, Usager $requerant): ?TestEligibiliteDysfonctionnement
    {
        $idTestEligibilite = $request->getSession()->get(SauvegarderTestEligibiliteDysfonctionnementEndpoint::CLEF_SESSION);
        if ($idTestEligibilite && null !== ($test = $this->em->find(TestEligibiliteDysfonctionnement::class, $idTestEligibilite))) {
            return $test;
        }

        // `dossier` est le côté inverse de la relation (mappedBy sur DemandeDysfonctionnement) : findOneBy() ne
        // peut pas filtrer dessus directement, d'où ce LEFT JOIN plutôt qu'un simple tableau de critères.
        return $this->em->createQueryBuilder()
            ->select('t')
            ->from(TestEligibiliteDysfonctionnement::class, 't')
            ->leftJoin('t.dossier', 'd')
            ->where('t.usager = :usager')
            ->andWhere('d.id IS NULL')
            ->setParameter('usager', $requerant)
            ->orderBy('t.dateSoumission', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    protected function getTestEligibiliteEnCours(Request $request, Usager $requerant): ?TestEligibiliteBrisPorte
    {
        $preInscription = $request->getSession()->get(PublicBrisPorteController::CLEF_SESSION_PREINSCRIPTION, []);
        $idTestEligibilite = $requerant->getNavigation()?->idTestEligibilite ?? @$preInscription['testEligibilite'] ?? null;

        return $idTestEligibilite ? $this->em->find(TestEligibiliteBrisPorte::class, $idTestEligibilite) : null;
    }

    protected function getDeclarationFDOEnCours(Request $request, Usager $requerant): ?DeclarationFDOBrisPorte
    {
        $preInscription = $request->getSession()->get(PublicBrisPorteController::CLEF_SESSION_PREINSCRIPTION, []);
        $idDeclarationFDO = $requerant->getNavigation()?->idDeclaration ?? $preInscription['declarationErreurOperationnelle'] ?? null;

        return $idDeclarationFDO ? $this->em->find(DeclarationFDOBrisPorte::class, $idDeclarationFDO) : null;
    }
}
