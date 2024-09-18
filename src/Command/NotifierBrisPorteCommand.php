<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Agent;
use App\Entity\BrisPorte;
use App\Service\DocumentManager;
use App\Service\Mailer\BasicMailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'precontentieux:notifier:bris-porte', description: "Notifier les agents d'un dépôt de dossier de pris de porte")]
class NotifierBrisPorteCommand extends Command
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly BasicMailer $mailer,
        protected readonly DocumentManager $documentManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getArgument('id');
        $brisPorte = $this->em->find(BrisPorte::class, $id);

        if (null === $brisPorte) {
            throw new \LogicException("Aucun bris de porte d'id $id");
        }

        foreach ($this->em->getRepository(Agent::class)->getAllActiveAgents() as $agent) {
            $this->mailer
                ->to($agent->getEmail())
                ->subject("Mon indemnisation justice: nouveau dossier d'indemnisation de bris de porte déposé")
                ->htmlTemplate('email/agent_nouveau_dossier_constitue.html.twig', [
                    'agent' => $agent,
                    'brisPorte' => $brisPorte,
                ]);
            foreach ($brisPorte->getLiasseDocumentaire()->getDocuments() as $document) {
                $this->mailer->addAttachment(
                    $this->documentManager->getDocumentBody($document),
                    $document
                );
            }

            $this->mailer->send(user: $agent);
        }

        return Command::SUCCESS;
    }
}
