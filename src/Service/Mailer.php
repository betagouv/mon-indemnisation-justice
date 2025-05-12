<?php

namespace MonIndemnisationJustice\Service;

use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\Requerant;
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

    public function toRequerant(Requerant $requerant): self
    {
        return $this->to($requerant->getEmail());
    }

    public function toAgent(Agent $agent): self
    {
        return $this->to($agent->getEmail());
    }

    public function to(string $email, ?string $nom = null): static
    {
        $this->email = new TemplatedEmail();
        $this->email->from(new Address($this->emailFrom, $this->emailFromLabel));
        $this->email->to($nom ? new Address($email, $nom) : $email);

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
