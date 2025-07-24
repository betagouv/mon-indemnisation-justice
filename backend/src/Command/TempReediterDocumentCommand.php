<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\EtatDossierType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;

#[AsCommand(name: 'mij:document:reediter', description: "Ré-éditer le corps des arrêtées de paiement qui n'en ont pas")]
class TempReediterDocumentCommand extends Command
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly Environment $twig,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $documents = $this->em->getRepository(Document::class)->findAll();

        foreach ($documents as $document) {
            if (
                DocumentType::TYPE_ARRETE_PAIEMENT === $document->getType()
                && EtatDossierType::DOSSIER_OK_VERIFIE === $document->getDossier()->getEtatDossier()->getEtat()
                && null === $document->getCorps()
            ) {
                $output->writeln("<info>Arrête de paiement {$document->getId()}</info>");
                $document->setCorps($this->twig->render('courrier/arretePaiement.html.twig', [
                    'dossier' => $document->getDossier(),
                ]));
                $this->em->persist($document);
            }
        }

        $this->em->flush();

        return Command::SUCCESS;
    }
}
