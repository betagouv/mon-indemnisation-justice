import React, {useState, useEffect, useContext} from 'react';

import { Button } from "@codegouvfr/react-dsfr/Button";
import { Document } from './PieceJointe/PieceJointe';
import { default as RecapitulatifBrisPorte } from './BrisPorte/Recapitulatif';
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import BrisPorte from './BrisPorte';
import User from './User';
import {DossierContext, PatchDossierContext} from "../contexts/DossierContext.ts";

const BrisPortePanel = function() {
  const dossier = useContext(DossierContext);

  const sections = [
    "Données personnelles",
    "Informations relatives au bris de porte",
    "Documents à joindre obligatoirement à votre demande",
    "Vérification et soumission de votre demande"
  ];
  const gotoFirstSection= () => gotoSection(0);
  const gotoSecondSection= () => gotoSection(1);
  const gotoThirdSection= () => gotoSection(2);
  const gotoSection = (index) => {
    setStep(index);
  }
  const [step,setStep]=useState(0);
  const [title,setTitle]=useState("");
  const [nextTitle,setNextTitle]=useState("");


  function incrementStep() { setStep(step+1);gotoStepper(); }
  function decrementStep() {
    setStep(step-1);
    gotoStepper();
  }
  function getCurrentStep() { return step+1; }
  function gotoStepper() { document.getElementById("pr-case_stepper")?.focus(); }

  useEffect(() => {
    setTitle(sections[step]);
    if(sections[step+1])
      setNextTitle(sections[step+1]);
  },[step]);

  return (
    <>
    <section tabIndex="0" className="pr-case_stepper" id="pr-case_stepper">
      <Stepper
        currentStep={getCurrentStep()}
        nextTitle={nextTitle}
        stepCount={sections.length}
        title={title}
      />
    </section>
    {(step<3) &&
    <p className="fr-my-4w">Sauf mention contraire, tous les champs sont obligatoires</p>
    }
    {(step===0) &&
    <section className="pr-case_form fr-mb-4w">
      <User />
      <div className="fr-grid-row fr-grid-row--gutters">
        <div className="fr-col-12">
          <Button onClick={incrementStep}>Valider et passer à l'étape suivante</Button>
        </div>
      </div>
    </section>
    }
    {(step===1) &&
    <>
      <BrisPorte />
      <div className="fr-col-12">
        <ul className="fr-btns-group fr-btns-group--inline-sm">
          <li>
            <Button onClick={incrementStep}>Valider et passer à l'étape suivante</Button>
          </li>
          <li>
            <Button onClick={decrementStep} priority="secondary">Revenir à l'étape précédente</Button>
          </li>
        </ul>
      </div>
    </>
    }
        {(step===2) &&
        <>
          <a name="pieces"></a>
          <div className="fr-grid-row fr-grid-row--gutters fr-mb-4w">
            <div className="fr-col-12">
              <section className="pr-form-section fr-p-4w">
                <Document
                  documents={dossier.liasseDocumentaire.documents.filter((document) => document.type === "attestation_information")}
                  libelle="Attestation complétée par les forces de l'ordre"
                  type={"attestation_information"}
                />
              </section>
            </div>
            <div className="fr-col-12">
              <section className="pr-form-section fr-p-4w">
                <Document
                  documents={dossier.liasseDocumentaire.documents.filter((document) => document.type === "photo_prejudice")}
                  libelle="Photos de la porte endommagée"
                  type={"photo_prejudice"}
                />
              </section>
            </div>
            {!(dossier.requerant.personneMorale != null) &&
            <div className="fr-col-12">
              <section className="pr-form-section fr-p-4w">
                <Document
                  documents={dossier.requerant.personnePhysique.liasseDocumentaire.documents.filter((document) => document.type === "carte_identite")}
                  libelle="Copie de votre pièce d'identité recto-verso"
                  type={"carte_identite"}
                />
              </section>
            </div>
            }
            <div className="fr-col-12">
              <section className="pr-form-section fr-p-4w">
                <Document
                  documents={dossier.liasseDocumentaire.documents.filter((document) => document.type === "preuve_paiement_facture")}
                  libelle="Facture acquittée attestant de la réalité des travaux de remise en état à l'identique "
                  type={"preuve_paiement_facture"}
                />
              </section>
            </div>
            {dossier.requerant.isPersonneMorale &&
            <div className="fr-col-12">
              <section className="pr-form-section fr-p-4w">
                <Document
                  documents={dossier.requerant.personnePhysique.liasseDocumentaire.documents.filter((document) => document.type === "rib")}
                  libelle="Relevé d'identité bancaire de votre société"
                  type={"rib"}
                />
              </section>
            </div>
            }
            {!dossier.requerant.isPersonneMorale &&
            <div className="fr-col-12">
              <section className="pr-form-section fr-p-4w">
                <Document
                  documents={dossier.requerant.personnePhysique.liasseDocumentaire.documents.filter((document) => document.type === "rib")}
                  libelle="Votre relevé d'identité bancaire"
                  type={"rib"}
                />
              </section>
            </div>
            }
            <div className="fr-col-12">
              <section className="pr-form-section fr-p-4w">
                <Document
                  documents={dossier.liasseDocumentaire.documents.filter((document) => document.type === "titre_propriete")}
                  libelle="Titre de propriété"
                  type={"titre_propriete"}
                />
              </section>
            </div>
            {!(dossier.requerant.personneMorale != null) &&
            <div className="fr-col-12">
              <section className="pr-form-section fr-p-4w">
                <Document
                  documents={dossier.liasseDocumentaire.documents.filter((document) => document.type === "contrat_location")}
                  libelle={"Contrat de location"}
                  type={"contrat_location"}
                />
              </section>
            </div>
            }
            <div className="fr-col-12">
              <ul className="fr-btns-group fr-btns-group--inline-sm">
                <li>
                  <Button onClick={incrementStep}>Valider et passer à l'étape suivante</Button>
                </li>
                <li>
                  <Button onClick={decrementStep} priority="secondary">Revenir à l'étape précédente</Button>
                </li>
              </ul>
            </div>
          </div>
        </>
        }
        {(step===3) &&
        <>

          <RecapitulatifBrisPorte
            gotoFirstSection={gotoFirstSection}
            gotoSecondSection={gotoSecondSection}
            gotoThirdSection={gotoThirdSection}
          />
          <div className="fr-col-12">
            <ul className="fr-btns-group fr-btns-group--inline-sm">
              <li>
                <Button
                  linkProps={{
                    href: Routing.generate('app_requerant_update_statut_to_constitue',{id: dossier.id})
                  }}
                >
                Je déclare mon bris de porte
                </Button>
              </li>
              <li>
                <Button onClick={decrementStep} priority="secondary">Revenir à l'étape précédente</Button>
              </li>
            </ul>
          </div>

        </>
        }

  </>
  );
}

export default BrisPortePanel;
