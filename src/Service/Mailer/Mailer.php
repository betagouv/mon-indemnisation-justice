<?php
namespace App\Service\Mailer;

use App\Entity\Requerant;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;

class Mailer
{
  private ?TransportInterface $_transport=null;

  public function __construct(
      TransportInterface $transport,
      protected readonly string $mailerFrom
  ) {
    $this->_transport=$transport;
  }

  public function getTransport(): ?TransportInterface { return $this->_transport; }

  public  function sendTo(Requerant $user, string $subject, string $html): self {
    $email = new Email();
    $email->from($this->mailerFrom);
    $email->to($user->getEmail());
    $email->subject($subject);
    $email->html($html);
    $this->getTransport()->send($email);
    return $this;
  }
}
