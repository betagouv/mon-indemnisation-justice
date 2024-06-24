<?php
namespace App\Service\Mailer;

use App\Contracts\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class SignedMailer implements MailerInterface {
  private ?TemplatedEmail $email = null;
  public function __construct(
    private VerifyEmailHelperInterface $verifyEmailHelper,
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

  public function send(?UserInterface $user=null, ?string $pathname=null): static
  {
    $signatureComponents = $this->verifyEmailHelper->generateSignature(
        $pathname,
        $user->getId(),
        $user->getEmail(),
        ['id' => $user->getId()]
    );

    $context = $this->email->getContext();
    $context['signedUrl'] = $signatureComponents->getSignedUrl();
    $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
    $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();

    $this->email->context($context);

    $this->mailer->send($this->email);
    return $this;
  }

  public function check(Request $request, UserInterface $user): bool {
    try {
      $this->verifyEmailHelper->validateEmailConfirmationFromRequest($request, $user->getId(), $user->getEmail());
      return true;
    }
    catch(VerifyEmailExceptionInterface $e) { return false; }
  }
}
