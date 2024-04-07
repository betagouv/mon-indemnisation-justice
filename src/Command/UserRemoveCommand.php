<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Utils\Validator\Validator;
use Doctrine\ORM\EntityManagerInterface;
use FOPG\Component\UtilsBundle\Env\Env;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:admin:suppression',
    description: 'Add a short description for your command',
)]
class UserRemoveCommand extends Command
{
    /** @var EntityManagerInterface */
    private $_em;
    /** @var Validator */
    private $_validator;

    public function getEntityManager(): ?EntityManagerInterface
    {
        return $this->_em;
    }

    public function getAccountRepository(): UserRepository
    {
      return $this
        ->getEntityManager()
        ->getRepository(User::class)
      ;
    }

    public function getValidator(): Validator
    {
      return $this->_validator;
    }

    public function __construct(EntityManagerInterface $em, Validator $validator)
    {
        $this->_em=$em;
        $this->_validator = $validator;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
          ->setDescription('Suppression d\'un compte')
          ->addArgument('email', InputArgument::REQUIRED, 'Email d\'utilisateur')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getEntityManager();
        /** @var SymfonyStyle $io */
        $io = new SymfonyStyle($input, $output);
        /** @var Validator $validator */
        $validator = $this->getValidator();
        /** @var string $email */
        $email = mb_strtolower($input->getArgument('email'));
        /** @var UserRepository $ar */
        $ar = $this->getAccountRepository();
        $io->text(' > <info>Email d\'utilisateur</info>: '.$email);
        /** @var ?User $account */
        $account = $ar->findOneBy(['email' => $email]);
        if(null === $account) {
          $io->error("Echec à la suppression. Le compte '$email' n'existe pas");
          return Command::FAILURE;
        }

        /** @var string $adminPassword Mot de passe administrateur pour la purge de la base de donnée */
        $targetPassword = Env::get('ADMIN_PASSWORD');
        $adminPassword = $io->askHidden('Saisissez votre mot de passe administrateur', [$validator, 'validatePassword']);
        if (hash("sha256", $adminPassword) !== $targetPassword) {
          $io->error('Le mot de passe administrateur est incorrect');
          return Command::FAILURE;
        }

        $ar->remove($account,true);

        return Command::SUCCESS;
    }
}
