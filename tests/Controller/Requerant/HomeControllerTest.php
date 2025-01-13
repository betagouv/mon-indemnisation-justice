<?php

namespace MonIndemnisationJustice\Tests\Controller\Requerant;

use MonIndemnisationJustice\Entity\Adresse;
use MonIndemnisationJustice\Entity\Civilite;
use MonIndemnisationJustice\Entity\PersonnePhysique;
use MonIndemnisationJustice\Entity\Requerant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class HomeControllerTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;
    protected UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        $requerant = $this->em
            ->getRepository(Requerant::class)
            ->findOneBy(['email' => 'raquel.randt@courriel.fr']);

        if (null !== $requerant) {
            $this->em->remove($requerant);
            $this->em->flush();
        }

        $requerant = (new Requerant())
            ->setAdresse(
                (new Adresse())
                ->setLigne1('12 rue des Oliviers')
                ->setLocalite('Nantes')
                ->setCodePostal('44100')
            )
            ->setPersonnePhysique(
                (new PersonnePhysique())
                ->setEmail('raquel.randt@courriel.fr')
                ->setCivilite(Civilite::MME)
                ->setPrenom1('Raquel')
                ->setNom('Randt')
            )
            ->setEmail('raquel.randt@courriel.fr')
            ->setRoles([Requerant::ROLE_REQUERANT])
        ;
        $requerant->setPassword($this->passwordHasher->hashPassword($requerant, 'P4ssword'));

        $this->em->persist($requerant);
        $this->em->flush();
    }

    public function testIndex()
    {
        $requerant = $this->em->getRepository(Requerant::class)->findOneBy(['email' => 'raquel.randt@courriel.fr']);

        $this->client->loginUser($requerant, 'requerant');

        $this->client->request('GET', '/requerant');

        $this->assertResponseIsSuccessful();
    }
}