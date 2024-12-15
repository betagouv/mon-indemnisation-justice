<?php

namespace App\Service;

use App\Entity\Agent;
use App\Entity\Document;
use App\Entity\Requerant;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\DataPart;

class Mailer
{
    public const BASE_DOMAIN = 'mon-indemnisation.anje-justice.fr';
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

    public function to(string $email): static
    {
        $this->email = new TemplatedEmail();
        $this->email->from(new Address($this->emailFrom, $this->emailFromLabel));
        $this->email->to($email);

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

    public function addAttachment(string $content, Document $document): static
    {
        $this->email->addPart(
            (new DataPart($content, $document->getOriginalFilename()))
            ->setContentId(sprintf('%s@%s', $document->getContentId(), self::BASE_DOMAIN))
        );

        return $this;
    }

    public function send(): ?SentMessage
    {
        $sent = $this->mailer->send($this->email);

        $this->email = null;

        return $sent;
    }
}
