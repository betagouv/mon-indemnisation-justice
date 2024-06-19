<?php
namespace App\Contracts;

use Symfony\Component\Security\Core\User\UserInterface;

interface MailerInterface
{
    public function from(string $emailFrom,string $emailLabel): static;
    public function to(string $emailTo): static;
    public function subject(string $subject): static;
    public function htmlTemplate(string $htmlTemplate, array $params=[]): static;
    public function send(?UserInterface $user=null, ?string $pathname=null): static;
}
