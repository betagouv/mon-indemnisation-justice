<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6\Dossier\Endpoint;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\MarquerDossierIndemniseEndpoint;
use MonIndemnisationJustice\Entity\EtatDossier;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(MarquerDossierIndemniseEndpoint::class)]
class MarquerDossierIndemniseEndpointTest extends AbstractEndpointTestCase
{
    public static function donnesMarquerDossierIndemnise()
    {
        return [
            'meme_jour' => [fn (EtatDossier $etat) => $etat->getDateEntree(), fn (EtatDossier $etat) => $etat->getDateEntree()],
            'date_1jour_trop_tot' => [fn (EtatDossier $etat) => $etat->getDateEntree()->sub(\DateInterval::createFromDateString('1 day')), fn (EtatDossier $etat) => $etat->getDateEntree()],
            'date_demain' => [fn (EtatDossier $etat) => new \DateTime()->add(\DateInterval::createFromDateString('1 day')), fn (EtatDossier $etat) => new \DateTime()],
        ];
    }

    /**
     * ETQ rédacteur du dossier, je dois pouvoir marquer le dossier comme indemnisé.
     */
    #[DataProvider('donnesMarquerDossierIndemnise')]
    public function testMarquerDossierIndemniseRedacteurOk(callable $calculerDateIndemnisationEnvoyee, callable $calculerDateIndemnisationAttendue)
    {

        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_OK_EN_ATTENTE_PAIEMENT);
        /** @var \DateTime $dateIndemnisationEnvoyee */
        $dateIndemnisationEnvoyee = $calculerDateIndemnisationEnvoyee($dossier->getEtatDossier());
        /** @var \DateTime $dateIndemnisationAttendue */
        $dateIndemnisationAttendue = $calculerDateIndemnisationAttendue($dossier->getEtatDossier());

        $this->connexionAgent($dossier->getRedacteur());

        // TODO tester avec une date invalide
        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/marquer-indemnise", [
            'dateIndemnisation' => $dateIndemnisationEnvoyee->format('Y-m-d'),
        ]);

        $this->assertTrue($this->client->getResponse()->isOk());

        $this->em->refresh($dossier);

        $this->assertEquals(EtatDossierType::DOSSIER_OK_INDEMNISE, $dossier->getEtatDossier()->getEtat());
        $this->assertEquals($dossier->getRedacteur(), $dossier->getEtatDossier()->getAgent());
        $this->assertEquals($dateIndemnisationAttendue->format('Y-m-d'), $dossier->getEtatDossier()->getDate()->format('Y-m-d'));
    }

    /**
     * ETQ agent de liaison, je dois pouvoir marquer le dossier comme indemnisé.
     */
    #[DataProvider('donnesMarquerDossierIndemnise')]
    public function testMarquerDossierIndemniseAgentLiaisonOk(callable $calculerDateIndemnisationEnvoyee, callable $calculerDateIndemnisationAttendue)
    {
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_OK_EN_ATTENTE_PAIEMENT);
        /** @var \DateTime $dateIndemnisationEnvoyee */
        $dateIndemnisationEnvoyee = $calculerDateIndemnisationEnvoyee($dossier->getEtatDossier());
        /** @var \DateTime $dateIndemnisationAttendue */
        $dateIndemnisationAttendue = $calculerDateIndemnisationAttendue($dossier->getEtatDossier());

        $agent = $this->connexion('liaison@justice.gouv.fr');

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/marquer-indemnise", [
            'dateIndemnisation' => $dateIndemnisationEnvoyee->format('Y-m-d'),
        ]);

        $this->assertTrue($this->client->getResponse()->isOk());

        $this->em->refresh($dossier);

        $this->assertEquals(EtatDossierType::DOSSIER_OK_INDEMNISE, $dossier->getEtatDossier()->getEtat());
        $this->assertEquals($agent, $dossier->getEtatDossier()->getAgent());
        $this->assertEquals($dateIndemnisationAttendue->format('Y-m-d'), $dossier->getEtatDossier()->getDate()->format('Y-m-d'));
    }
}
