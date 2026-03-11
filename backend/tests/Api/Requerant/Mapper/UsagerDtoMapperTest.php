<?php

namespace MonIndemnisationJustice\Tests\Api\Requerant\Mapper;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\UsagerDto;
use MonIndemnisationJustice\Entity\Personne;
use MonIndemnisationJustice\Entity\Usager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

class UsagerDtoMapperTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private ObjectMapperInterface $mapper;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->mapper = self::getContainer()->get(ObjectMapperInterface::class);
    }

    public function testUsagerToUsagerDtoMapping(): void
    {
        $usager = $this->em->getRepository(Usager::class)->findOneBy(['email' => 'ray.keran@courriel.fr']);

        $this->assertInstanceOf(Usager::class, $usager, 'No usagers found in fixtures');
        $this->assertInstanceOf(Personne::class, $usager->getPersonne(), 'No usagers found in fixtures');

        $usagerDto = $this->mapper->map($usager, UsagerDto::class);

        $this->assertInstanceOf(UsagerDto::class, $usagerDto);

        $this->assertNotNull($usagerDto->id);
        $this->assertNotNull($usagerDto->personne);
        $this->assertNotNull($usagerDto->personne->nom);
        $this->assertNotNull($usagerDto->personne->prenom);
        $this->assertNotNull($usagerDto->personne->courriel);
    }
}
