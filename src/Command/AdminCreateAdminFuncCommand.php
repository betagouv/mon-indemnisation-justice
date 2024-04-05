<?php

namespace App\Command;

use App\Entity\User;
use App\Service\PasswordGenerator;
use App\Utils\Validator\Validator;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOPG\Component\UtilsBundle\Env\Env;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:admin:ajout',
    description: 'Création d\'un administrateur fonctionnel',
)]
class AdminCreateAdminFuncCommand extends Command
{
    use EmailTraitCommand;
    
    /** @var EntityManagerInterface */
    private $_em;
    /** @var UserPasswordHasherInterface */
    private $_passwordHasher;

    public function getEntityManager(): ?EntityManagerInterface {
      return $this->_em;
    }

    public function getValidator(): Validator
    {
      return $this->_validator;
    }

    public function __construct(
      EntityManagerInterface $em,
      UserPasswordHasherInterface $passwordHasher,
      Validator $validator
    ) {
        $this->_validator = $validator;
        $this->_passwordHasher = $passwordHasher;
        $this->_em = $em;
        parent::__construct();
    }

    public function getPasswordHasher(): UserPasswordHasherInterface
    {
      return $this->_passwordHasher;
    }

    public function getAccountRepository(): UserRepository
    {
      return $this
        ->getEntityManager()
        ->getRepository(User::class)
      ;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Création d\'un nouveau compte')
            ->addArgument('username', InputArgument::REQUIRED, 'Nom d\'utilisateur')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getEntityManager();
        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = $this->getPasswordHasher();
        /** @var SymfonyStyle $io */
        $io = new SymfonyStyle($input, $output);
        /** @var string $username */
        $username = mb_strtolower($input->getArgument('username'));
        /** @var UserRepository $ar */
        $ar = $this->getAccountRepository();
        $io->text(' > <info>Nom d\'utilisateur</info>: '.$username);
        /** @var ?User $account */
        $account = $ar->findOneBy(['username' => $username]);
        /** @var ?string $password */
        $password = null;
        /** @var Validator $validator */
        $validator = $this->getValidator();
        $isAdmin = true;
        if(null !== $account) {
          $io->error("Echec à l'ajout. Le compte $username existe déjà");
          return Command::FAILURE;
        }

        /** @var string $adminPassword Mot de passe administrateur pour la purge de la base de donnée */
        $targetPassword = Env::get('ADMIN_PASSWORD');
        $adminPassword = $io->askHidden('Saisissez votre mot de passe administrateur', [$validator, 'validatePassword']);
        if (hash("sha256", $adminPassword) !== $targetPassword) {
          $io->error('Le mot de passe administrateur est incorrect');
          return Command::FAILURE;
        }

        $password = PasswordGenerator::new();

        $check = false;
        do {
          /** @var ?string $email */
          $email = mb_strtolower($io->ask('Email', null, [$validator, 'validateEmail']));
          $account = $ar->findOneByEmail($email);
          if(null !== $account)
            $io->error("L'email $email est déjà déclaré. Veuillez en saisir un autre");
          else
            $check = true;
        }while(false === $check);

        /**
         * @author yroussel
         *
         * Création d'un nouveau compte
         */
        $account = new User();
        $account->setUsername($username);
        $account->setEmail($email);
        $account->setPassword("FAKE_PASSWORD");
        $account->setDateChangementMDP(null);

        if (true === $isAdmin) {
          $io->text(' > <info>Rôle</info>: '.User::ROLE_ADMIN_FONC);
          $account->addRole(User::ROLE_ADMIN_FONC);
        }

        $account->setPassword(
            $passwordHasher->hashPassword($account, $password)
        );
        $ar->add($account, true);

        $html = str_replace([
          "{{username}}",
          "{{password}}"
        ],[
          $account->getUsername(),
          $password
        ],"
        <div>
        <p>Bonjour,<br>
        PRECONTENTIEUX vous a inscrit comme administrateur fonctionnel de l'application. Vous aurez la charge
        de l'administration des utilisateurs habilités à utiliser PRECONTENTIEUX.</p>
        <p>Vos accès: <br>
          <table>
            <tr>
              <th>Nom d'utilisateur</th><td>{{username}}</td>
            </tr>
            <tr>
              <th>Mot de passe</th><td>{{password}}</td>
            </tr>
          </table>
        </p>
        </div>
        ");

        # envoi de l'email à l'administrateur fonctionnel
        $this->send(
          to: $account->getEmail(),
          subject: 'Création de votre compte administrateur fonctionnel pour PRECONTENTIEUX',
          html: $html
        );

        $io->success("Le compte $username a été ajouté avec succès !");
        return Command::SUCCESS;
    }
}
