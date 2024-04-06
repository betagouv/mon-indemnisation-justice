<?php

namespace App\Command;

use FOPG\Component\UtilsBundle\Env\Env;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;

trait EmailTraitCommand {
  private ?TransportInterface $_transport=null;

  public function getMailer(): ?TransportInterface {
    return $this->_transport;
  }

  public function setMailer(TransportInterface $mailer): self {
    $this->_transport=$mailer;

    return $this;
  }

  public function send(string $to, string $subject, string $html): self {
    # envoi de l'email à l'administrateur fonctionnel
    $email = new Email();
    $email->from(Env::get('MAILER_FROM'));
    $email->to($to);
    $email->subject($subject);
    $email->html($html);
    $this->getMailer()->send($email);

    return $this;
  }
}
