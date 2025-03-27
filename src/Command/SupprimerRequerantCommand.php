<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Requerant;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mij:requerant:supprimer', description: 'Supprimer requérant')]
class SupprimerRequerantCommand extends Command
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('courriel', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $courriel = $input->getArgument('courriel');
        $requerant = $this->em->getRepository(Requerant::class)->findOneBy(['email' => $courriel]);

        if (null == $requerant) {
            throw new \LogicException("Aucun requérant associé au courriel $courriel");
        }

        $this->em->remove($requerant);
        $this->em->flush();

        return Command::SUCCESS;
    }
}
