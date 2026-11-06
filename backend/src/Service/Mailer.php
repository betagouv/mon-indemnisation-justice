<?php

namespace MonIndemnisationJustice\Service;

use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\EnvoiCourriel;
use MonIndemnisationJustice\Entity\EnvoiCourrielType;
use MonIndemnisationJustice\Entity\Usager;
use MonIndemnisationJustice\Repository\EnvoiCourrielRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;

class Mailer
{
    // TODO ré-écrire pour que l'email sous-jacent sois éditer façon "builder"
    private ?TemplatedEmail $email = null;

    public function __construct(
        private TransportInterface $mailer,
        protected readonly string $emailFrom,
        protected readonly string $emailFromLabel,
        protected readonly CollecteurEvenementBrevo $collecteurEvenementBrevo,
        protected readonly EnvoiCourrielRepository $envoiCourrielRepository,
        protected readonly LoggerInterface $logger,
    ) {
    }

    public function from(): static
    {
        return $this;
    }

    public function toRequerant(Usager $requerant): self
    {
        return $this->to($requerant->getEmail());
    }

    /**
     * @param Agent[] $copies
     *
     * @return $this
     */
    public function toAgent(Agent $agent, array $copies = []): self
    {
        return $this->to($agent->getEmail(), copies: array_map(fn (Agent $a) => new Address($a->getEmail(), $a->getNomComplet(true)), $copies));
    }

    /**
     * @param array<Address|string> $copies
     *
     * @return $this
     */
    public function to(string $email, ?string $nom = null, array $copies = []): static
    {
        $this->email = new TemplatedEmail();
        $this->email->from(new Address($this->emailFrom, $this->emailFromLabel));
        $this->email->to($nom ? new Address($email, $nom) : $email);

        foreach ($copies as $copie) {
            $this->email->cc($copie);
        }

        return $this;
    }

    public function subject(string $subject): static
    {
        $this->email->subject($subject);

        return $this;
    }

    public function htmlTemplate(string $htmlTemplate, array $params = []): static
    {
        $this->email->htmlTemplate($htmlTemplate);
        $this->email->context($params);

        return $this;
    }

    /**
     * @param EnvoiCourrielType|null       $type        le type d'envoi de courriel à suivre. Si ce type est fourni, le mailer crée une
     *                                                  instance de `EnvoiCourriel` et programme le suivi via le `CollecteurEvenementBrevo`
     * @param DeclarationFDOBrisPorte|null $declaration la déclaration de bris de porte à rattacher à l'envoi de courriel qui sera généré
     * @param bool                         $flush       si `false`, l'instance de `EnvoiCourriel` est seulement persistée : c'est
     *                                                  alors à l'appelant de flusher (utile depuis un listener Doctrine)
     *
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function send(?EnvoiCourrielType $type = null, ?DeclarationFDOBrisPorte $declaration = null, bool $flush = true): ?SentMessage
    {
        if (null !== $type) {
            try {
                // Une erreur lors de la création d'un suivi de courriel ne doit pas empêcher le courriel de partir
                $envoiCourriel = new EnvoiCourriel()
                    ->setRecepteur($this->email->getTo()[0]?->toString() ?? $this->emailFrom)
                    ->setType($type)
                    ->setDeclaration($declaration);

                $this->envoiCourrielRepository->save($envoiCourriel, $flush);

                foreach ($this->collecteurEvenementBrevo->genererEnTetesCourriel($envoiCourriel) as $name => $value) {
                    $this->email->getHeaders()->addTextHeader($name, $value);
                }
            } catch (\Throwable $t) {
                $this->logger->error("La création d'un suivi de courriel a échoué", [
                    'motif' => $t->getMessage(),
                    'type' => $type->value,
                    'declaration' => $declaration?->getId()?->toString(),
                ]);
            }
        }


        $sent = $this->mailer->send($this->email);

        $this->email = null;

        return $sent;
    }
}
