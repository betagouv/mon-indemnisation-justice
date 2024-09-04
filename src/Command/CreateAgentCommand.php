<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Agent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'precontentieux:creer-agent', description: 'Créer un agent')]
class CreateAgentCommand extends Command
{
    public function __construct(
        protected readonly EntityManagerInterface $manager,
        protected readonly UserPasswordHasherInterface $hasher
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('prenom', InputArgument::REQUIRED)
            ->addArgument('nom', InputArgument::REQUIRED)
            ->addArgument('email', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::REQUIRED)
            ->addOption('est-gestionnaire', null, InputOption::VALUE_NONE)
            ->addOption('est-validateur', null, InputOption::VALUE_NONE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $agent = (new Agent())
            ->setEmail($input->getArgument('email'))
            ->addRole(Agent::ROLE_AGENT_REDACTEUR)
            ->setActive(true)
            ->setPrenom($input->getArgument('prenom'))
            ->setNom($input->getArgument('nom'));

        if ($input->getOption('est-gestionnaire')) {
            $agent->addRole(Agent::ROLE_AGENT_GESTION_PERSONNEL);
        }

        if ($input->getOption('est-validateur')) {
            $agent->addRole(Agent::ROLE_AGENT_VALIDATEUR);
        }

        $agent
            ->setPassword($this->hasher->hashPassword($agent, $input->getArgument('password') ?? self::generatePassword(12)))
            ->setDateChangementMDP(new \DateTime());

        $this->manager->persist($agent);
        $this->manager->flush();

        return Command::SUCCESS;
    }

    protected static function generatePassword($length = 10)
    {
        return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', (int) ceil($length / strlen($x)))), 1, $length);
    }
}
