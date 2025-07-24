<?php

namespace MonIndemnisationJustice\Api\Agent\Resources\Transformer;

use MonIndemnisationJustice\Api\Agent\Resources\Output\DossierATransmettreOutput;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\EtatDossierType;
use Symfony\Component\ObjectMapper\TransformCallableInterface;

class DossierTransformer implements TransformCallableInterface
{
    public function __invoke(mixed $value, object $source, ?object $target): mixed
    {
        if ($source instanceof BrisPorte) {
            if ($value instanceof DossierATransmettreOutput) {
                return $this->versDossierATransmettre($source);
            }
        }

        return null;
    }

    protected function versDossierATransmettre(BrisPorte $dossier): DossierATransmettreOutput
    {
        $output = new DossierATransmettreOutput();

        $output->id = $dossier->id;
        $output->reference = $dossier->getReference();
        $output->requerant = $dossier->getRequerant()->getNomCourant();
        $output->montantIndemnisation = $dossier->getMontantIndemnisation();

        $etatValidation = $dossier->getEtat(EtatDossierType::DOSSIER_OK_A_INDEMNISER);

        $output->dateValidation = $etatValidation->getDate();
        $output->agentValidateur = $etatValidation->getAgent()->getNomComplet();

        return $output;
    }
}
