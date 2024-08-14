<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Utils\Validator\Validator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UserListCommand extends Command
{
    public static $defaultName = "app:admin:liste";
    /** @var EntityManagerInterface */
    private $_em;
    /** @var UserRepository */
    private $_ar;
    /** @var Validator */
    private $_validator;

    public function __construct(
      EntityManagerInterface $em,
      UserRepository $ar,
      Validator $validator
    ) {
      parent::__construct();
      $this->_em = $em;
      $this->_ar = $ar;
      $this->_validator = $validator;
    }

    public function getEntityManager(): EntityManagerInterface
    {
      return $this->_em;
    }

    public function getAccountRepository(): UserRepository
    {
      return $this->_ar;
    }

    public function getValidator(): Validator
    {
      return $this->_validator;
    }

    protected function configure(): void
    {
        $this
          ->setDescription("Liste des utilisateurs déclarés")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getEntityManager();
        /** @var SymfonyStyle $io */
        $io = new SymfonyStyle($input, $output);
        /** @var UserRepository $ar */
        $ar = $this->getAccountRepository();
        /** @var Collection<int, Account> */
        $allUsers = $ar->findAdminFoncs();
        // Doctrine query returns an array of objects and we need an array of plain arrays
        $usersAsPlainArrays = array_map(function (User $user) {
            return [
                $user->getId(),
                $user->getUsername(),
                $user->getEmail(),
            ];
        }, $allUsers);

        $io->table(
            ['ID', "Nom d'utilisateur", 'Email'],
            $usersAsPlainArrays
        );

        return Command::SUCCESS;
    }
}
