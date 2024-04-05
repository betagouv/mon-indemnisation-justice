<?php

namespace App\Command;

use FOPG\Component\UtilsBundle\Env\Env;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

trait EmailTraitCommand {
  private ?MailerInterface $_mailer=null;

  public function getMailer(): ?MailerInterface {
    return $this->_mailer;
  }

  public function setMailer(MailerInterface $mailer): self {
    $this->_mailer=$mailer;

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
