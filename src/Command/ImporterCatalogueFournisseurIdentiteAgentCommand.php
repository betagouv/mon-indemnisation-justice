<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\FournisseurIdentiteAgent;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[AsCommand(name: 'mon_indemnisation:agent:importer_catalogue_fournisseur_identite', description: 'Hello PhpStorm')]
class ImporterCatalogueFournisseurIdentiteAgentCommand extends Command
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
            ->addArgument('path', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getArgument('path');
        if (!filter_var($path, FILTER_VALIDATE_URL) && !is_file($path)) {
            $output->writeln("<error>URL ou chemin de fichier inconnu $path</error>");

            return Command::FAILURE;
        }

        $index = 0;
        if (($handle = fopen($path, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if ($index++ > 0) {
                    dump($data);
                    list($reseau, $uid, $titre, $actif, $urlDecouverte, $listeFQDNs) = $data;

                    /** @var FournisseurIdentiteAgent $fournisseurIdentite */
                    $fournisseurIdentite = $this->em->getRepository(FournisseurIdentiteAgent::class)->findOneBy(['uid' => $uid]) ?? (new FournisseurIdentiteAgent())->setUid($uid);

                    $fournisseurIdentite
                        ->setNom($titre)
                        ->setActif(in_array(strtolower($actif), ['oui', 'true', 1]))
                        ->setUrlDecouverte($urlDecouverte)
                        ->setReseauInterne(FournisseurIdentiteAgent::RESEAU_INTERNE === $reseau)
                        ->setDomaines(explode(',', $listeFQDNs))
                    ;
                    $this->em->persist($fournisseurIdentite);
                }
            }
        }

        fclose($handle);
        $this->em->flush();

        return Command::SUCCESS;
    }
}
