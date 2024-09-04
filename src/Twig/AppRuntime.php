<?php

namespace App\Twig;

use App\Entity\Agent;
use App\Entity\LiasseDocumentaire;
use App\Entity\PersonneMorale;
use App\Entity\PersonnePhysique;
use App\Entity\Requerant;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Extension\RuntimeExtensionInterface;

class AppRuntime implements RuntimeExtensionInterface
{
    public function estRequerant(?UserInterface $user = null): bool
    {
        return $user instanceof Requerant;
    }

    public function estAgent(?UserInterface $user = null): bool
    {
        return $user instanceof Agent;
    }

    public function spellout(float $amount, string $locale = 'fr'): string
    {
        $t = new \NumberFormatter($locale, \NumberFormatter::SPELLOUT);
        $numberParsing = explode('.', number_format(round($amount, 2, PHP_ROUND_HALF_DOWN), 2, '.', ''));
        $_1 = $t->format($numberParsing[0]);
        $_2 = $t->format($numberParsing[1]);
        $output = str_replace(['$1', '$2'], [$_1, $_2], '$1 euros et $2 centimes');

        return $output;
    }

    public function emptyUser(): Requerant
    {
        $liasseDocumentaire = new LiasseDocumentaire();
        $reflectionClass = new \ReflectionClass(LiasseDocumentaire::class);
        $reflectionClass->getProperty('id')->setValue($liasseDocumentaire, 0);

        $personnePhysique = new PersonnePhysique();
        $reflectionClass = new \ReflectionClass(PersonnePhysique::class);
        $reflectionClass->getProperty('id')->setValue($personnePhysique, 0);
        $personnePhysique->setLiasseDocumentaire($liasseDocumentaire);

        $personneMorale = new PersonneMorale();
        $reflectionClass = new \ReflectionClass(PersonneMorale::class);
        $reflectionClass->getProperty('id')->setValue($personneMorale, 0);
        $personneMorale->setLiasseDocumentaire($liasseDocumentaire);

        $user = new Requerant();
        $reflectionClass = new \ReflectionClass(Requerant::class);
        $reflectionClass->getProperty('id')->setValue($user, 0);
        $reflectionClass->getProperty('adresse')->setValue($user, null);
        $reflectionClass->getProperty('personnePhysique')->setValue($user, $personnePhysique);
        $reflectionClass->getProperty('personneMorale')->setValue($user, $personneMorale);

        return $user;
    }
}
