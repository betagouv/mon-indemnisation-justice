<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use MonIndemnisationJustice\Entity\Document;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;

#[AsCommand(name: 'mij:document:reparer', description: 'Réparer les documents où des informations sont manquantes')]
class ReparerDocumentsCommand extends Command
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        #[Target('default.storage')]
        protected readonly FilesystemOperator $storage,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Document $document */
        foreach ($this->em->getRepository(Document::class)->findAll() as $document) {
            if (!$document->getSize()) {
                $document->setSize($this->storage->fileSize($document->getFilename()));

                $this->em->persist($document);
            }
        }

        $this->em->flush();

        return Command::SUCCESS;
    }
}
