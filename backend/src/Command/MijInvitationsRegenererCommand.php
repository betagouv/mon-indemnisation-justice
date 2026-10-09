<?php

namespace MonIndemnisationJustice\Command;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Service\GenerateurCodeInvitation;
use MonIndemnisationJustice\Service\Mail\EnvoiInvitationDeposer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Régénère le code d'invitation de toutes les déclarations FDO.
 *
 * Le mail d'invitation avec le nouveau lien n'est envoyé qu'aux déclarations sans dossier : les autres passent déjà
 * par le compte du requérant.
 *
 * Simulation par défaut, les modifications ne sont appliquées qu'avec `--execute`.
 */
#[AsCommand(
    name: 'mij:invitations:regenerer',
    description: 'Régénère le code d\'invitation de toutes les déclarations et renvoie le mail aux déclarations sans dossier',
)]
class MijInvitationsRegenererCommand extends Command
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GenerateurCodeInvitation $generateurCodeInvitation,
        protected readonly EnvoiInvitationDeposer $envoiInvitationDeposer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('execute', null, InputOption::VALUE_NONE, 'Applique les modifications et envoie les mails (sinon simulation)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $execute = (bool) $input->getOption('execute');

        $repository = $this->em->getRepository(DeclarationFDOBrisPorte::class);
        $declarations = $repository->findAll();

        // Calcul de tous les nouveaux codes avant toute écriture
        $nouveauxCodes = [];
        foreach ($declarations as $declaration) {
            do {
                $code = $this->generateurCodeInvitation->generer();
            } while (null !== $repository->findOneBy(['reference' => $code]));

            $nouveauxCodes[spl_object_id($declaration)] = $code;
        }

        $sansDossier = array_filter($declarations, fn (DeclarationFDOBrisPorte $declaration) => !$declaration->estAttribue());

        $io->section($execute ? 'Application' : 'Simulation');
        $io->listing([
            sprintf('Déclarations au total : %d', count($declarations)),
            sprintf('Codes à régénérer : %d', count($nouveauxCodes)),
            sprintf('Déclarations sans dossier (mail d\'invitation) : %d', count($sansDossier)),
        ]);

        if (!$execute) {
            $io->note('Simulation : aucune modification. Relancer avec --execute pour appliquer.');

            return Command::SUCCESS;
        }

        // Codes enregistrés en base avant tout envoi, pour ne pas diffuser un lien qui n'existe pas
        foreach ($declarations as $declaration) {
            $declaration->setReference($nouveauxCodes[spl_object_id($declaration)]);
        }
        $this->em->flush();

        foreach ($sansDossier as $declaration) {
            $this->envoiInvitationDeposer->envoyer($declaration);
        }

        $io->success(sprintf('%d codes régénérés, %d mails envoyés.', count($declarations), count($sansDossier)));

        return Command::SUCCESS;
    }
}
