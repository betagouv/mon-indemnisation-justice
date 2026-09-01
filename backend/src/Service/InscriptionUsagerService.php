<?php

namespace MonIndemnisationJustice\Service;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Public\Dysfonctionnement\Input\InscriptionUsagerInput;
use MonIndemnisationJustice\Api\Public\Dysfonctionnement\SauvegarderTestEligibiliteDysfonctionnementEndpoint;
use MonIndemnisationJustice\Entity\Personne;
use MonIndemnisationJustice\Entity\TestEligibiliteDysfonctionnement;
use MonIndemnisationJustice\Entity\Usager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Logique de création de compte partagée entre l'inscription usager (PP/PM) et l'inscription avocat,
 * qui n'en diffère qu'en ajoutant le rattachement à un Avocat une fois le compte créé.
 */
class InscriptionUsagerService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly Mailer $mailer,
    ) {
    }

    /**
     * Crée et persiste (sans flush) l'usager et le rattache au test d'éligibilité de la session courante, le cas échéant.
     */
    public function creerUsager(InscriptionUsagerInput $input, Request $request): Usager
    {
        $usager = new Usager()
            ->setEmail($input->courriel)
            ->setPersonne(
                new Personne()
                    ->setCivilite($input->civilite)
                    ->setPrenom($input->prenom)
                    ->setNom($input->nom)
                    ->setNomNaissance($input->nomNaissance ?? $input->nom)
                    ->setCourriel($input->courriel)
                    ->setTelephone($input->telephone)
            )
            ->setPersonneMorale($input->estPersonneMorale);

        $usager->setPassword($this->userPasswordHasher->hashPassword($usager, $input->motDePasse));
        $usager->genererJetonVerification();

        $this->em->persist($usager);

        $testId = $request->getSession()->get(SauvegarderTestEligibiliteDysfonctionnementEndpoint::CLEF_SESSION);
        if ($testId) {
            $test = $this->em->getRepository(TestEligibiliteDysfonctionnement::class)->find($testId);
            if (null !== $test) {
                $test->usager = $usager;
                $this->em->persist($test);
            }
        }

        return $usager;
    }

    public function envoyerEmailActivation(Usager $usager): void
    {
        $this->mailer
            ->toRequerant($usager)
            ->subject("Activation de votre compte sur l'application Mon Indemnisation Justice")
            ->htmlTemplate('email/inscription_a_finaliser.html.twig', ['usager' => $usager])
            ->send();
    }
}
