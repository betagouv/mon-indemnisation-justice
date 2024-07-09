<?php

namespace App\Command;

use App\Entity\User;
use App\Service\Mailer\BasicMailer;
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
use Symfony\Component\Mailer\Transport\TransportInterface;

#[AsCommand(
    name: 'app:admin:ajout',
    description: 'Création d\'un compte agent',
)]
class UserAddNewCommand extends Command
{
    /** @var EntityManagerInterface */
    private $_em;
    /** @var UserPasswordHasherInterface */
    private $_passwordHasher;
    /** @var BasicMailer $mailer */
    private $mailer;

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
      Validator $validator,
      BasicMailer $mailer
    ) {
        $this->_validator = $validator;
        $this->_passwordHasher = $passwordHasher;
        $this->_em = $em;
        $this->mailer = $mailer;
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
            ->addArgument('username', InputArgument::REQUIRED, 'Nom technique d\'utilisateur')
            ->addArgument('prenom', InputArgument::REQUIRED, 'Prénom d\'utilisateur')
            ->addArgument('nom', InputArgument::REQUIRED, 'Nom d\'utilisateur')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $env = Env::get('APP_ENV');
        /** @var array $tos Liste des receveurs du mail de création */
        $tos = [];
        /**
         * @author yanroussel
         *        En environnement de dev, l'envoi des informations de connexion
         *        pour les comptes agents seront redirigés vers un compte structurel
         */
        if($env === 'dev' && Env::get('DEFAULT_EMAIL_RECEIVER'))
          $tos[]=Env::get('DEFAULT_EMAIL_RECEIVER');

        /** @var EntityManagerInterface $em */
        $em = $this->getEntityManager();
        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = $this->getPasswordHasher();
        /** @var SymfonyStyle $io */
        $io = new SymfonyStyle($input, $output);

        /** @var string $username */
        $username = mb_strtolower($input->getArgument('username'));
        /** @var string $prenom */
        $prenom = mb_strtolower($input->getArgument('prenom'));
        /** @var string $nom */
        $nom = mb_strtolower($input->getArgument('nom'));
        /** @var UserRepository $ar */
        $ar = $this->getAccountRepository();
        $io->text(' > <info>Nom d\'utilisateur</info>: '.$username);
        /** @var ?User $account */
        $account = $ar->findOneBy(['username' => $username]);
        /** @var ?string $password */
        $password = null;
        /** @var Validator $validator */
        $validator = $this->getValidator();
        //$isAdmin = true;
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
        $account->setIsVerified(true);
        $account->getPersonnePhysique()->setNomNaissance($nom);
        $account->getPersonnePhysique()->setPrenom1($prenom);
        $tab = [
          User::ROLE_REDACTEUR_PRECONTENTIEUX => 'Rédacteur',
          User::ROLE_CHEF_PRECONTENTIEUX => 'Chef de pôle',
          User::ROLE_ADMIN_FONC => 'Administrateur fonctionnel'
        ];
        $role = $io->choice('Rôle', $tab);
        $io->text(' > <info>Rôle</info>: '.$role);
        $account->addRole($role);

        $account->setPassword(
            $passwordHasher->hashPassword(
              $account,
              $password
            )
        );

        $ar->add($account, true);

        # envoi de l'email à l'administrateur fonctionnel
        $tos[]=$account->getEmail();
        $this
          ->mailer
          ->from(Env::get('EMAIL_FROM'), Env::get('EMAIL_FROM_LABEL'))
          ->subject('Création de votre compte pour PRECONTENTIEUX')
          ->htmlTemplate('admin/email/ajout_utilisateur.html.twig',[
            'url' => Env::get('BASE_URL'),
            'username' => $account->getUsername(),
            'target_email' => $account->getEmail(),
            'password' => $password,
          ])
        ;
        foreach($tos as $to)
          $this->mailer->to($to);
        $this->mailer->send();

        $io->success("Le compte $username a été ajouté avec succès. Les informations de connexion ont été envoyé à ".implode(" et ",$tos)." !");

        if(Env::get('APP_ENV') === 'dev')
          $io->note('(email:'.$account->getEmail().',password:'.$password.')');
        return Command::SUCCESS;
    }
}
