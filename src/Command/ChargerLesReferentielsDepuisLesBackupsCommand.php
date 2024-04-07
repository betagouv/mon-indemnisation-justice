<?php

namespace App\Command;

use App\Entity\Civilite;

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
    name: 'app:admin:charger-les-referentiels-depuis-les-backups',
    description: 'Chargement des référentiels à partir des fichiers SQL backup',
)]
class ChargerLesReferentielsDepuisLesBackupsCommand extends AbstractCommand
{
    private ?EntityManagerInterface $_em=null;

    public function __construct(
      ContainerInterface $container,
      EntityManagerInterface $em
    ) {
        $this->_em = $em;
        parent::__construct($container);
    }

    public function getEntityManager(): ?EntityManagerInterface {
      return $this->_em;
    }

    public function getRepository(string $classname): mixed {
      return $this->_em->getRepository($classname);
    }

    protected function configure(): void
    {
        parent::configure();
        $this
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $this->logTitle("Lancement de la lecture des référentiels en sauvegarde");
        $refFolder = realpath(__DIR__.'/../../docs/referentiel');
        $referentiels = [
          Civilite::class
        ];

        foreach($referentiels as $referentiel) {

          $this->logNote('Import du référentiel "'.$referentiel.'"');
          $this->importCSV($referentiel, $refFolder);
        }

        $this->logSuccess('Import des référentiels réalisés avec succès');

        return Command::SUCCESS;
    }

    public function importCSV(string $classname, $folder): void
    {
        $term = preg_quote('\\');
        $filename = StringFacility::toSnakeCase(preg_replace("/^(.*)".$term."(?<filename>[^".$term."]+)$/","$2",$classname));
        $handle = fopen("$folder/$filename.sql","r");
        if($handle) {
          $conn = $this->getEntityManager()->getConnection();
          $conn->getConfiguration()->setSQLLogger(null);
          while (($sql = fgets($handle)) !== false) {
            $conn
              ->prepare($sql)
              ->execute()
            ;
          }
        }
    }
}
