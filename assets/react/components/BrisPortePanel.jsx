import React, {useState, useEffect, useContext} from 'react';

import { Button } from "@codegouvfr/react-dsfr/Button";
import { Document } from './PieceJointe/PieceJointe';
import { Br } from "../utils/fundamental";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import BrisPorte from './BrisPorte';
import User from './User';

const BrisPortePanel = function() {

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
    /*
    const userUri = Routing.generate('_api_requerant_get',{id: user.id});
    const prejudiceUri = Routing.generate('_api_bris_porte_get',{id: brisPorte.id});
    Promise
      .all([userUri,prejudiceUri]
        .map((u) => fetch(u).then((response) => response.json()))
      )
      .then(([u_d,bp_d]) => {
        setUser(u_d);
        setBrisPorte(bp_d);
        setStep(index);
      })
      .catch(() => {})
    ;
     */
    setStep(index);
  }
  const [step,setStep]=useState(0);
  const [title,setTitle]=useState("");
  const [nextTitle,setNextTitle]=useState("");


  function incrementStep() { setStep(step+1);gotoStepper(); }
  function decrementStep() {
    /*
    const userUri = Routing.generate('_api_requerant_get',{id:user.pId});
    const prejudiceUri = Routing.generate('_api_bris_porte_get',{id:brisPorte.id});
    Promise
      .all([userUri,prejudiceUri]
        .map((u) => fetch(u).then((response) => response.json()))
      )
      .then(([u_d,bp_d]) => {
        setUser(u_d);
        setBrisPorte(bp_d);
      })
      .catch(() => {})
    ;
    */
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
                  liasseDocumentaireIri={dossier.liasseDocumentaire}
                  label="Attestation complétée par les forces de l'ordre"
                  type={"attestation_information"}
                />
              </section>
            </div>
            <div className="fr-col-12">
              <section className="pr-form-section fr-p-4w">
                <Document
                  liasseDocumentaireIri={dossier.liasseDocumentaire}
                  label="Photos de la porte endommagée"
                  type={"photo_prejudice"}
                />
              </section>
            </div>
            {!(dossier.requerant.personneMorale != null) &&
            <div className="fr-col-12">
              <section className="pr-form-section fr-p-4w">
                <Document
                  liasseDocumentaireIri={dossier.liasseDocumentaire}
                  label="Copie de votre pièce d'identité recto-verso"
                  hint_text=" "
                  type={"carte_identite"}
                />
              </section>
            </div>
            }
            <div className="fr-col-12">
              <section className="pr-form-section fr-p-4w">
                <Document
                  liasseDocumentaireIri={dossier.liasseDocumentaire}
                  label="Facture acquittée attestant de la réalité des travaux de remise en état à l'identique "
                  hint_text=" "
                  type={"preuve_paiement_facture"}
                />
              </section>
            </div>
            {(dossier.requerant.personneMorale != null) &&
            <div className="fr-col-12">
              <section className="pr-form-section fr-p-4w">
                <Document
                  liasseDocumentaireIri={dossier.liasseDocumentaire}
                  label="Relevé d'identité bancaire de votre société"
                  hint_text=" "
                  type={"rib"}
                />
              </section>
            </div>
            }
            {!(dossier.requerant.personneMorale != null) &&
            <div className="fr-col-12">
              <section className="pr-form-section fr-p-4w">
                <Document
                  liasseDocumentaireIri={dossier.liasseDocumentaire}
                  label="Votre relevé d'identité bancaire"
                  hint_text=" "
                  type={"rib"}
                />
              </section>
            </div>
            }
            <div className="fr-col-12">
              <section className="pr-form-section fr-p-4w">
                <Document
                  liasseDocumentaireIri={dossier.liasseDocumentaire}
                  label="Titre de propriété"
                  hint_text=" "
                  type={"titre_propriete"}
                />
              </section>
            </div>
            {!(dossier.requerant.personneMorale != null) &&
            <div className="fr-col-12">
              <section className="pr-form-section fr-p-4w">
                <Document
                  liasseDocumentaireIri={dossier.liasseDocumentaire}
                  label={"Contrat de location"}
                  hint_text={""}
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
          {/*
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
          */}
        </>
        }
      <div className="fr-col-12">
        <Br space={2}/>
      </div>

  </>
  );
}

export default BrisPortePanel;
