<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6\Dossier\Endpoint;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\ConsulterDossierEndpoint;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ConsulterDossierEndpoint::class)]
class ConsulterDossierEndpointTest extends AbstractEndpointTestCase
{
    public function testConsulterDossierOk(): void
    {
        $this->connexion('attributeur@justice.gouv.fr');
        $dossier = $this->em->getRepository(Dossier::class)->findOneBy(['reference' => 'BRI/20250103/001']);


        $this->client->request('GET', "/api/agent/fip6/dossier/{$dossier->getId()}");

        $this->assertTrue($this->client->getResponse()->isOk());

        $reponse = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayEquals([
            'id' => $dossier->getId(),
            'reference' => $dossier->getReference(),
            'etat' => [
                'id' => $dossier->getEtatDossier()->getId(),
                'etat' => $dossier->getEtatDossier()->getEtat()->value,
                'dateEntree' => $dossier->getEtatDossier()->getDate()->format('Y-m-d H:i:s'),
                'redacteur' => $dossier->getRedacteur()->getId(),
                'requerant' => null != $dossier->getEtatDossier()->getRequerant(),
                'contexte' => null,
            ],
            'dateDepot' => $dossier->getDateDeclaration()?->format('Y-m-d H:i:s'),
            'redacteur' => $dossier->getRedacteur() ? [
                'id' => $dossier->getRedacteur()->getId(),
                'nom' => $dossier->getRedacteur()->getNomComplet(capital: true),
            ] : null,
            'usager' => ['id' => $dossier->getUsager()->getId(),
                'civilite' => $dossier->getUsager()->getPersonne()->getCivilite()->value,
                'nom' => $dossier->getUsager()->getPersonne()->getNom(),
                'prenom' => $dossier->getUsager()->getPersonne()->getPrenom(),
            ],
            'notes' => null,
            'testEligibilite' => [
                'estVise' => $dossier->getBrisPorte()->getTestEligibilite()->estVise,
                'estHebergeant' => $dossier->getBrisPorte()->getTestEligibilite()->estHebergeant,
                'rapportAuLogement' => $dossier->getBrisPorte()->getTestEligibilite()->rapportAuLogement->value,
                'aContacteAssurance' => $dossier->getBrisPorte()->getTestEligibilite()->aContacteAssurance,
                'aContacteBailleur' => $dossier->getBrisPorte()->getTestEligibilite()->aContacteBailleur,
            ],
            'qualiteRequerant' => $dossier->getBrisPorte()->getRapportAuLogement()->value,
            'estEligible' => $dossier->getBrisPorte()->getTestEligibilite()?->estEligible(),
            'declarationFDO' => null,
            'dateOperation' => null,
            'descriptionRequerant' => $dossier->getBrisPorte()->getDescriptionRequerant(),
            'estPorteBlindee' => false,
            'documents' => [],
            'montantIndemnisation' => null,
            'adresse' => [
                'ligne1' => $dossier->getBrisPorte()->getAdresse()->getLigne1(),
                'ligne2' => $dossier->getBrisPorte()->getAdresse()->getLigne2(),
                'codePostal' => $dossier->getBrisPorte()->getAdresse()->getCodePostal(),
                'localite' => $dossier->getBrisPorte()->getAdresse()->getLocalite(),
            ],
            'requerant' => [
                'civilite' => $dossier->getRequerantPersonne()->getCivilite()->value,
                'nom' => $dossier->getRequerantPersonne()->getNom(),
                'nomNaissance' => $dossier->getRequerantPersonne()->getNomNaissance(),
                'prenoms' => [
                    $dossier->getRequerantPersonnePhysique()->getPersonne()->getPrenom(),
                    $dossier->getRequerantPersonnePhysique()->getPrenom2(),
                    $dossier->getRequerantPersonnePhysique()->getPrenom3(),
                ],
                'courriel' => $dossier->getUsager()->getEmail(),
                'telephone' => $dossier->getRequerantPersonnePhysique()?->getPersonne()->getTelephone(),
                'dateNaissance' => $dossier->getRequerantPersonnePhysique()?->getDateNaissance()->format('Y-m-d H:i:s'),
                'communeNaissance' => $dossier->getRequerantPersonnePhysique()?->getCommuneNaissance(),
                'paysNaissance' => $dossier->getRequerantPersonnePhysique()?->getPaysNaissance()?->getNom(),
                'raisonSociale' => $dossier->getRequerantPersonneMorale()?->getRaisonSociale(),
                'siren' => $dossier->getRequerantPersonneMorale()?->getSirenSiret(),
                'typePersonneMorale' => $dossier->getRequerantPersonneMorale()?->getType()?->value,
            ],
            'typeAttestation' => null,
            'typeAdministration' => null,
        ], $reponse);
    }
}
