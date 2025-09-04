<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\EtatDossier;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Service\Mailer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mij:dossier:reparer', description: 'Réparer les dossiers qui auraient été créés à vide')]
class DossierReparerCommand extends Command
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly Mailer $mailer,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('id', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getArgument('id');
        $dossier = $this->em->find(BrisPorte::class, $id);

        if (null == $dossier) {
            throw new \LogicException("Aucun dossier trouve pour l'id $id");
        }

        if (!in_array($dossier->getEtatDossier()?->getEtat(), [EtatDossierType::DOSSIER_A_FINALISER, EtatDossierType::DOSSIER_A_INSTRUIRE, EtatDossierType::DOSSIER_EN_INSTRUCTION])) {
            throw new \LogicException('Le dossier est déjà trop avancé pour être réparé');
        }

        // Faire partir un email d'avertissement
        $this->mailer
            ->toRequerant($dossier->getRequerant())
            ->subject("Mon Indemnisation Justice: vous pouvez poursuivre votre demande d'indemnisation")
            ->htmlTemplate('email/message_dossier_repare.twig', [
                'dossier' => $dossier,
            ]);

        $this->mailer->send();

        // Revenir à l'état "A_FINALISER" :
        $dossier->setHistoriqueEtats(
            $dossier->getHistoriqueEtats()->filter(fn (EtatDossier $etat) => EtatDossierType::DOSSIER_A_FINALISER != $etat->getEtat())->toArray()
        );
        $dossier->setRedacteur(null);

        $this->em->persist($dossier);
        $this->em->flush();

        return Command::SUCCESS;
    }
}
