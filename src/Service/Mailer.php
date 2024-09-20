<?php

namespace App\Service;

use App\Entity\Document;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Security\Core\User\UserInterface;

class Mailer
{
    const BASE_DOMAIN = "mon-indemnisation.anje-justice.fr";
    private ?TemplatedEmail $email = null;

    public function __construct(
        private TransportInterface $mailer,
        protected readonly string $emailFrom,
        protected readonly string $emailFromLabel,
    ) {}

    public function from(string $emailFrom, string $emailLabel): static
    {
        return $this;
    }

    public function to(string $emailTo): static
    {
        $this->email = new TemplatedEmail();
        $this->email->from(new Address($this->emailFrom, $this->emailFromLabel));
        $this->email->to($emailTo);

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
            ->setContentId(sprintf("%s@%s", $document->getContentId(), self::BASE_DOMAIN))
        );

        return $this;
    }

    public function send(?UserInterface $user = null, ?string $pathname = null): static
    {
        $this->mailer->send($this->email);

        return $this;
    }
}
