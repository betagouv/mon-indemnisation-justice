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

#[AsCommand(name: 'mij:document:inventaire', description: "Mène l'inventaire des documents présentement en base de donnée")]
class InventaireDocumentsCommand extends Command
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
            if (!$this->storage->fileExists($document->getFilename())) {
                $output->writeln("<error>Le document {$document->getId()} n'a pas de fichier correspondant</error>");
            } elseif (($mime = $this->storage->mimeType($document->getFilename())) !== $document->getMime()) {
                $output->writeln("<error>Le document {$document->getId()} n'a pas le bon type (attendu {$document->getMime()}, détecté {$mime})</error>");
            } else {
                $output->writeln("<info>Document {$document->getId()} valide</info>");
            }
        }

        return Command::SUCCESS;
    }
}
