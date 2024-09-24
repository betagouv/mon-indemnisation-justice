<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\BrisPorte;
use App\Event\BrisPorteConstitueEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[AsCommand(name: 'precontentieux:notifier:bris-porte', description: "Notifier les agents d'un dépôt de dossier de pris de porte")]
class NotifierBrisPorteCommand extends Command
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getArgument('id');
        /** @var BrisPorte $brisPorte */
        $brisPorte = $this->em->find(BrisPorte::class, $id);

        if (null === $brisPorte) {
            throw new \LogicException("Aucun bris de porte d'id $id");
        }

        if (!$brisPorte->getDateDeclaration()) {
            throw new \LogicException("Le dossier de bris de porte d'id $id n'a pas encore été constitué");
        }

        $this->eventDispatcher->dispatch(new BrisPorteConstitueEvent($brisPorte));

        return Command::SUCCESS;
    }
}
