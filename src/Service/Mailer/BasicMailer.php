<?php
namespace App\Service\Mailer;

use App\Contracts\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class BasicMailer implements MailerInterface {
  private ?TemplatedEmail $email = null;
  public function __construct(
    private TransportInterface $mailer,
  )
  {
    $this->email = new TemplatedEmail();
  }

  public function from(string $emailFrom,string $emailLabel): static
  {
      $this->email->from(new Address($emailFrom,$emailLabel));

      return $this;
  }

  public function to(string $emailTo): static
  {
      $this->email->to($emailTo);

      return $this;
  }

  public function subject(string $subject): static
  {
      $this->email->subject($subject);

      return $this;
  }

  public function htmlTemplate(string $htmlTemplate, array $params=[]): static
  {
      $this->email->htmlTemplate($htmlTemplate);
      $this->email->context($params);
      return $this;
  }

  public function send(?UserInterface $user=null,?string $pathname=null): static
  {
    $this->mailer->send($this->email);
    return $this;
  }
}
