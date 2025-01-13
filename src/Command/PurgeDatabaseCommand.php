<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(name: 'precontentieux:base:purger', description: 'Supprimer toutes les tables et séquences')]
class PurgeDatabaseCommand extends Command
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        #[Autowire('%env(APP_ENV)%')]
        protected readonly string $environment
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->environment === 'prod') {
            throw new \LogicException("Purge de la base de données refusée en production");
        }

        $this->em->getConnection()->beginTransaction();

        try {
            // 1. Suppression de toutes les tables
            $tables = $this->em->getConnection()->executeQuery("select tablename from pg_tables where schemaname = current_schema() and tableowner = current_user")->fetchFirstColumn();

            foreach ($tables as $table) {
                $this->em->getConnection()->executeQuery(sprintf('drop table if exists "%s" cascade', $table));
            }

            $output->writeln(sprintf("Suppression de %d table(s)", count($tables)));

            // 2. Suppression de toutes les tables
            $sequences = $this->em->getConnection()->executeQuery("select relname as sequence from pg_class where relkind = 'S'")->fetchFirstColumn();

            foreach ($sequences as $sequence) {
                $this->em->getConnection()->executeQuery(sprintf('drop sequence if exists "%s" cascade', $sequence));
            }

            $output->writeln(sprintf("Suppression de %d sequence(s)", count($sequences)));
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }

        $this->em->getConnection()->commit();

        return Command::SUCCESS;
    }
}
