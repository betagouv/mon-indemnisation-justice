<?php

namespace App\Command;

use App\Utils\Validator\Validator;
use Doctrine\ORM\EntityManagerInterface;
use FOPG\Component\UtilsBundle\Command\AbstractCommand;
use FOPG\Component\UtilsBundle\Env\Env;
use FOPG\Component\UtilsBundle\String\StringFacility;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[AsCommand(
    name: 'app:admin:purge-de-la-bdd',
    description: "Fonction dédiée à la purge de la base de donnée pour l'expérimentation",
)]
class AdminDestructionDeLaBddCommand extends AbstractCommand
{
    private ?Validator $_validator=null;

    public function __construct(ContainerInterface $container, Validator $validator) {
        parent::__construct($container);
        $this->_validator = $validator;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    public function getValidator(): ?Validator
    {
        return $this->_validator;
    }

    protected function configure(): void
    {
        parent::configure();
        $this
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getIO();
        /** @var string $adminPassword Mot de passe administrateur pour la purge de la base de donnée */
        $targetPassword = Env::get('ADMIN_PASSWORD');
        $this->logTitle('Lancement de la commande de suppression des données contenues dans la base de donnée');
        $validator = $this->getValidator();
        $adminPassword = $io->askHidden('Saisissez votre mot de passe administrateur', [$validator, 'validatePassword']);
        $confirmAdminPassword = $io->askHidden('Resaisissez votre mot de passe administrateur', [$validator, 'validatePassword']);

        if($adminPassword !== $confirmAdminPassword)
        {
          $this->logError('Les mots de passe administrateur sont différents');
          return Command::FAILURE;
        }

        if (hash("sha256", $adminPassword) !== $targetPassword) {
          $this->logError('Le mot de passe administrateur est incorrect');
          return Command::FAILURE;
        }

        $this->purge();

        $this->logSuccess('La purge de la base de donnée a été effectuée avec succès');
        return Command::SUCCESS;
    }

    public function purge(): void {
        $conn = $this->getEntityManager()->getConnection();

        $sqls = [
          'delete from public.statut cascade;',
          'delete from public.bris_porte cascade;',
          'delete from public.prejudice cascade;',
          'delete from public.tracking cascade;',
          'delete from public.user cascade;',
          'delete from public.adresse cascade;',
          'delete from public.service_enqueteur cascade;',
          'delete from public.personne_physique cascade;',
          'delete from public.personne_morale cascade;',
        ];
        foreach($sqls as $sql) {
          $conn->exec($sql);
        }
    }
}
