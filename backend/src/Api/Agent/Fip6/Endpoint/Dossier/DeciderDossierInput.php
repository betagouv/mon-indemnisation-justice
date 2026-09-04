<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use MonIndemnisationJustice\Entity\MotifRejetBrisPorte;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[Assert\Callback(callback: 'validerDecision')]
final class DeciderDossierInput
{
    public ?float $montantIndemnisation = null;

    #[Assert\Choice(callback: [MotifRejetBrisPorte::class, 'cases'], message: 'Le motif de rejet doit correspondre à un cas connu')]
    public ?MotifRejetBrisPorte $motifRejet = null;

    public function validerDecision(ExecutionContextInterface $context): void
    {
        if ((null !== $this->montantIndemnisation) === (null !== $this->motifRejet)) {
            $context->buildViolation("Vous devez renseigner soit un montant d'indemnisation, soit un motif de rejet, mais pas les deux")
                ->addViolation();
        }
    }
}
