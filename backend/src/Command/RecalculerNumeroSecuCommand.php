<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\PersonnePhysique;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mij:secu:recalculer', description: 'Supprimer requérant')]
class RecalculerNumeroSecuCommand extends Command
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $personnes = $this->em->getRepository(PersonnePhysique::class)->findAll();

        foreach ($personnes as $personne) {
            if (null !== $personne->getCivilite() && null !== $personne->getDateNaissance() && (null !== $personne->getCodePostalNaissanceCode() || !$personne->getPaysNaissance()?->estFrance())) {
                $personne->recalculerNumeroSecuriteSociale();
                $this->em->persist($personne);
            }
        }

        $this->em->flush();

        return Command::SUCCESS;
    }
}
