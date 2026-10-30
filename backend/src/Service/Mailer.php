<?php

namespace MonIndemnisationJustice\Service;

use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\Usager;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;

class Mailer
{
    private ?TemplatedEmail $email = null;

    public function __construct(
        private TransportInterface $mailer,
        protected readonly string $emailFrom,
        protected readonly string $emailFromLabel,
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

    public function send(): ?SentMessage
    {
        $sent = $this->mailer->send($this->email);

        $this->email = null;

        return $sent;
    }
}
