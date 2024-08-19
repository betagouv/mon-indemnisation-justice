<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Civilite;
use App\Entity\PersonnePhysique;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'precontentieux:create_redacteur', description: 'Créer un rédacteur')]
class CreateRedacteurCommand extends Command
{
    public function __construct(
        protected readonly EntityManagerInterface $manager,
        protected readonly UserPasswordHasherInterface $hasher
    )
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('prenom', InputArgument::REQUIRED)
            ->addArgument('nom', InputArgument::REQUIRED)
            ->addArgument('email', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::REQUIRED)
            ->addArgument('civilite', InputArgument::OPTIONAL, '', 'mme')
            ->addOption('isAdmin', 'a', InputOption::VALUE_NONE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $civilite = $this->manager->getRepository(Civilite::class)->findOneBy([
            'mnemo' => $input->getArgument('civilite')
        ]);

        $redacteur = (new User())
            ->setUsername(
                sprintf(
                    '%s.%s',
                    strtolower($input->getArgument('prenom')),
                    strtolower($input->getArgument('nom'))
                )
            )
            ->setEmail($input->getArgument('email'))
            ->addRole($input->getOption('isAdmin') ? User::ROLE_CHEF_PRECONTENTIEUX : User::ROLE_REDACTEUR_PRECONTENTIEUX)
            ->setIsVerified(true)
            ->setActive(true)
            ->setPersonnePhysique(
                (new PersonnePhysique())
                    ->setCivilite($civilite)
                    ->setPrenom1($input->getArgument('prenom'))
                    ->setNom($input->getArgument('prenom'))
            );
        $redacteur
            ->setPassword($this->hasher->hashPassword($redacteur, $input->getArgument('password')))
            ->setDateChangementMDP(new \DateTime());

        $this->manager->persist($redacteur);
        $this->manager->flush();

        return Command::SUCCESS;
    }
}
