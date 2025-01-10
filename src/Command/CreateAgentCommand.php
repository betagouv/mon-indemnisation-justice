<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Repository\AgentRepository;
use MonIndemnisationJustice\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'precontentieux:agent:creer', description: 'Créer un agent')]
class CreateAgentCommand extends Command
{
    public function __construct(
        protected readonly AgentRepository $agentRepository,
        protected readonly Mailer $mailer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('prenom', InputArgument::REQUIRED)
            ->addArgument('nom', InputArgument::REQUIRED)
            ->addArgument('email', InputArgument::REQUIRED)
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

        /*
        $agent
            ->setPassword($this->hasher->hashPassword($agent, $input->getArgument('password') ?? self::generatePassword(12)))
            ->setDateChangementMDP(new \DateTime());
        */

        $agent->genererJetonVerification();
        $this->mailer
                ->toAgent($agent)
                ->subject('Création de votre compte rédacteur sur Mon Indemnisation Justice')
                ->htmlTemplate('email/agent/activation_reinitialisation.html.twig', [
                    'agent' => $agent,
                ])
                ->send();

        $this->agentRepository->save($agent);

        return Command::SUCCESS;
    }
}
