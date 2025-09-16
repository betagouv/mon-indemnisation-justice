<?php

namespace MonIndemnisationJustice\Event\Listener;

use MonIndemnisationJustice\Event\Event\DossierArreteEditeEvent;
use MonIndemnisationJustice\Event\Event\DossierArreteSigneEvent;
use MonIndemnisationJustice\Event\Event\DossierAttribueEvent;
use MonIndemnisationJustice\Event\Event\DossierClotureEvent;
use MonIndemnisationJustice\Event\Event\DossierDeposeEvent;
use MonIndemnisationJustice\Event\Event\DossierInstruitPropositionEvent;
use MonIndemnisationJustice\Event\Event\DossierInstruitRejetEvent;
use MonIndemnisationJustice\Event\Event\DossierPropositionAccepteeEvent;
use MonIndemnisationJustice\Event\Event\DossierPropositionEnvoyeeEvent;
use MonIndemnisationJustice\Event\Event\DossierRejeteEvent;
use MonIndemnisationJustice\Repository\AgentRepository;
use MonIndemnisationJustice\Service\Mailer;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: DossierClotureEvent::class, method: 'dossierCloture')]
#[AsEventListener(event: DossierDeposeEvent::class, method: 'dossierDepose')]
#[AsEventListener(event: DossierAttribueEvent::class, method: 'dossierAttribue')]
#[AsEventListener(event: DossierInstruitPropositionEvent::class, method: 'dossierInstruitProposition')]
#[AsEventListener(event: DossierPropositionEnvoyeeEvent::class, method: 'dossierPropositionEnvoyee')]
#[AsEventListener(event: DossierPropositionAccepteeEvent::class, method: 'dossierPropositionAcceptee')]
#[AsEventListener(event: DossierArreteEditeEvent::class, method: 'dossierArreteEdite')]
#[AsEventListener(event: DossierArreteSigneEvent::class, method: 'dossierArreteSigne')]
#[AsEventListener(event: DossierInstruitRejetEvent::class, method: 'dossierInstruitRejet')]
#[AsEventListener(event: DossierRejeteEvent::class, method: 'dossierRejete')]
class DossierTransitionListener
{
    public function __construct(protected readonly Mailer $mailer, protected readonly AgentRepository $agentRepository) {}

    public function dossierCloture(DossierClotureEvent $evenement): void
    {
        // Informer le requérant que son dossier est clos :
        // Envoi du mail de confirmation.
        $this->mailer
            ->toRequerant($evenement->dossier->getRequerant())
            ->subject("Clôture du dossier {$evenement->dossier->getReference()}")
            ->htmlTemplate('email/requerant/dossier_cloture.html.twig', [
                'dossier' => $evenement->dossier,
            ])
            ->send()
        ;
    }

    public function dossierDepose(DossierDeposeEvent $evenement): void
    {
        // Informer le requérant que son dossier est bien déposé :
        $this->mailer
            ->toRequerant($evenement->dossier->getRequerant())
            ->subject('Votre déclaration de bris de porte a bien été prise en compte')
            ->htmlTemplate('email/requerant/dossier_depose.html.twig', [
                'dossier' => $evenement->dossier,
            ])
            ->send()
        ;
        // Prévenir les attributeurs qu'un nouveau dossier attend d'être attribué :
        foreach ($this->agentRepository->getAttributeurs() as $attributeur) {
            $this->mailer
                ->toAgent($attributeur)
                ->subject('Mon Indemnisation Justice: vous avez un nouveau dossier à attribuer')
                ->htmlTemplate('email/agent/fip3/dossier_a_attribuer.twig', [
                    'agent' => $attributeur,
                    'dossier' => $evenement->dossier,
                ])
                ->send()
            ;
        }
    }

    public function dossierAttribue(DossierAttribueEvent $evenement): void
    {
        // Prévenir le rédacteur qu'il / elle a été attribué à un nouveau dossier qu'il / elle doit instruire :
        $this->mailer
            ->toAgent($evenement->dossier->getRedacteur())
            ->subject('Mon Indemnisation Justice: vous avez un nouveau dossier à instruire')
            ->htmlTemplate('email/agent/fip3/dossier_a_instruire.twig', [
                'agent' => $evenement->dossier->getRedacteur(),
                'dossier' => $evenement->dossier,
            ])
            ->send()
        ;
    }

    public function dossierInstruitProposition(DossierInstruitPropositionEvent $evenement): void
    {
        // Prévenir le validateur qu'il doit signer le courrier de proposition d'indemnisation :
        foreach ($this->agentRepository->getValidateurs() as $validateur) {
            $this->mailer
                ->toAgent($validateur)
                ->subject("Mon Indemnisation Justice: vous avez une nouvelle proposition d'indemnisation à signer")
                ->htmlTemplate('email/agent/fip3/dossier_proposition_a_signer.twig', [
                    'agent' => $validateur,
                    'dossier' => $evenement->dossier,
                ])
                ->send()
            ;
        }
    }

    public function dossierPropositionEnvoyee(DossierPropositionEnvoyeeEvent $evenement): void
    {
        // Prévenir le requérant qu'une décision l'attend sur son espace :
        $this->mailer
            ->toRequerant($evenement->dossier->getRequerant())
            ->subject("Mon Indemnisation Justice: votre demande d'indemnisation a obtenu une réponse")
            ->htmlTemplate('email/requerant/dossier_decide.twig', [
                'dossier' => $evenement->dossier,
            ])
            ->send()
        ;
    }

    public function dossierPropositionAcceptee(DossierPropositionAccepteeEvent $evenement): void
    {
        // Prévenir le rédacteur qu'une déclaration d'acceptation lui a été retournée sur son dossier :
        $this->mailer
            ->toAgent($evenement->dossier->getRedacteur())
            ->subject("Mon Indemnisation Justice: vous avez reçu une déclaration d'acceptation à vérifier")
            ->htmlTemplate('email/agent/fip3/dossier_proposition_acceptee.twig', [
                'agent' => $evenement->dossier->getRedacteur(),
                'dossier' => $evenement->dossier,
            ])
        ;
    }

    public function dossierArreteEdite(DossierArreteEditeEvent $evenement): void
    {
        // Prévenir le validateur qu'un arrêté de paiement attend sa signature :
        foreach ($this->agentRepository->getValidateurs() as $validateur) {
            $this->mailer
                ->toAgent($validateur)
                ->subject('Mon Indemnisation Justice: vous avez un nouvel arrêté de paiement à signer')
                ->htmlTemplate('email/agent/fip3/dossier_arrete_a_signer.twig', [
                    'agent' => $validateur,
                    'dossier' => $evenement->dossier,
                ])
                ->send()
            ;
        }
    }

    public function dossierArreteSigne(DossierArreteSigneEvent $evenement): void
    {
        // Prévenir l'agent de liaison avec le bureau du budget qu'un dossier est prêt pour transmission :
        foreach ($this->agentRepository->getAgentsLiaisonBudget() as $agentLiaisonBudget) {
            $this->mailer
                ->toAgent($agentLiaisonBudget)
                ->subject('Mon Indemnisation Justice: vous avez un nouveau dossier à transmettre à FIP6')
                ->htmlTemplate('email/agent/fip3/dossier_a_transmettre.twig', [
                    'agent' => $agentLiaisonBudget,
                    'dossier' => $evenement->dossier,
                ])
                ->send()
            ;
        }
    }

    public function dossierInstruitRejet(DossierInstruitRejetEvent $evenement): void
    {
        // Prévenir le requérant qu'une décision l'attend sur son espace :
        $this->mailer
            ->toRequerant($evenement->dossier->getRequerant())
            ->subject("Mon Indemnisation Justice: votre demande d'indemnisation a obtenu une réponse")
            ->htmlTemplate('email/requerant/dossier_decide.twig', [
                'dossier' => $evenement->dossier,
            ])
            ->send();
    }
}
