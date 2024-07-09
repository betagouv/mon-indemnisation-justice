import React, {useState,useEffect} from 'react';
import { fr } from "@codegouvfr/react-dsfr";
import { Table } from "@codegouvfr/react-dsfr/Table";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { Document } from './PieceJointe/PieceJointe';
import {default as RecapitulatifBrisPorte} from './BrisPorte/Recapitulatif';
import { Br } from "../utils/fundamental";
import { trans, BRIS_PORTE_SECTION,USER_SECTION,
  DOCUMENT_ATTESTATION_INFORMATION_TITLE,
  DOCUMENT_PHOTO_BRIS_PORTE_TITLE,
  DOCUMENT_FACTURE_TITLE,
  DOCUMENT_RIB_PRO_TITLE,
  DOCUMENT_RIB_PRO_HINT,
  DOCUMENT_RIB_HINT,
  DOCUMENT_RIB_TITLE,
  DOCUMENT_PIECE_IDENTITE_TITLE,
  DOCUMENT_PIECE_IDENTITE_HINT,
  DOCUMENT_FACTURE_HINT,
  DOCUMENT_TITRE_PROPRIETE_TITLE,
  DOCUMENT_TITRE_PROPRIETE_HINT,
  DOCUMENT_PHOTO_BRIS_PORTE_HINT,
  GLOBAL_STEP_NEXT,GLOBAL_STEP_PREVIOUS,
  BRIS_PORTE_EDIT_UPDATE_CONSTITUE,
  GLOBAL_INFORMATIONS_REQUIREMENT,
  VERIFICATION_SECTION,
  BRIS_PORTE_PJ_SECTION
} from '../../translator';
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import BrisPorte from './BrisPorte';
import User from './User';

const BrisPortePanel = function({id,user,brisPorte}) {

  const sections = [
    trans(USER_SECTION),
    trans(BRIS_PORTE_SECTION),
    trans(BRIS_PORTE_PJ_SECTION),
    trans(VERIFICATION_SECTION)
  ];
  const gotoFirstSection= () => gotoSection(0);
  const gotoSecondSection= () => gotoSection(1);
  const gotoThirdSection= () => gotoSection(2);
  const gotoSection = (index) => {
    const userUri = Routing.generate('_api_user_get',{id:user.pId});
    const prejudiceUri = Routing.generate('_api_bris_porte_get',{id:brisPorte.id});
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
  }
  const [step,setStep]=useState(0);
  const [title,setTitle]=useState("");
  const [nextTitle,setNextTitle]=useState("");
  const [isPersonneMorale,setIsPersonneMorale]=useState(user.isPersonneMorale);
  const [_brisPorte,setBrisPorte]=useState(brisPorte);
  const [_user,setUser]=useState(user);
  function incrementStep() { setStep(step+1);gotoStepper(); }
  function decrementStep() {
    const userUri = Routing.generate('_api_user_get',{id:user.pId});
    const prejudiceUri = Routing.generate('_api_bris_porte_get',{id:brisPorte.id});
    Promise
      .all([userUri,prejudiceUri]
        .map((u) => fetch(u).then((response) => response.json()))
      )
      .then(([u_d,bp_d]) => {
        setUser(u_d);
        setBrisPorte(bp_d);
        setStep(step-1);
        gotoStepper();
      })
      .catch(() => {})
    ;
  }
  function getCurrentStep() { return step+1; }
  function gotoStepper() { $("#pr-case_stepper").focus(); }

  const toggleIsPersonneMorale = () => setIsPersonneMorale(!isPersonneMorale);

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
    <p className="fr-my-4w">{trans(GLOBAL_INFORMATIONS_REQUIREMENT)}</p>
    }
    {(step===0) &&
    <section className="pr-case_form fr-mb-4w">
      <User user={_user} id={id} toggleIsPersonneMorale={toggleIsPersonneMorale}/>
      <div className="fr-grid-row fr-grid-row--gutters">
        <div className="fr-col-12">
          <Button onClick={incrementStep}>{trans(GLOBAL_STEP_NEXT)}</Button>
        </div>
      </div>
    </section>
    }
    {(step===1) &&
    <>
      <BrisPorte brisPorte={_brisPorte} />
      <div className="fr-col-12">
        <ul className="fr-btns-group fr-btns-group--inline-sm">
          <li>
            <Button onClick={incrementStep}>{trans(GLOBAL_STEP_NEXT)}</Button>
          </li>
          <li>
            <Button onClick={decrementStep} priority="secondary">{trans(GLOBAL_STEP_PREVIOUS)}</Button>
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
                  liasseDocumentaireIri={brisPorte.liasseDocumentaire}
                  label={trans(DOCUMENT_ATTESTATION_INFORMATION_TITLE)}
                  type={"attestation_information"}
                />
              </section>
            </div>
            <div className="fr-col-12">
              <section className="pr-form-section fr-p-4w">
                <Document
                  liasseDocumentaireIri={brisPorte.liasseDocumentaire}
                  label={trans(DOCUMENT_PHOTO_BRIS_PORTE_TITLE)}
                  hint_text={trans(DOCUMENT_PHOTO_BRIS_PORTE_HINT)}
                  type={"photo_prejudice"}
                />
              </section>
            </div>
            {!isPersonneMorale &&
            <div className="fr-col-12">
              <section className="pr-form-section fr-p-4w">
                <Document
                  liasseDocumentaireIri={user.personnePhysique.liasseDocumentaire}
                  label={trans(DOCUMENT_PIECE_IDENTITE_TITLE)}
                  hint_text={trans(DOCUMENT_PIECE_IDENTITE_HINT)}
                  type={"carte_identite"}
                />
              </section>
            </div>
            }
            <div className="fr-col-12">
              <section className="pr-form-section fr-p-4w">
                <Document
                  liasseDocumentaireIri={brisPorte.liasseDocumentaire}
                  label={trans(DOCUMENT_FACTURE_TITLE)}
                  hint_text={trans(DOCUMENT_FACTURE_HINT)}
                  type={"preuve_paiement_facture"}
                />
              </section>
            </div>
            {isPersonneMorale &&
            <div className="fr-col-12">
              <section className="pr-form-section fr-p-4w">
                <Document
                  liasseDocumentaireIri={user.personneMorale.liasseDocumentaire}
                  label={trans(DOCUMENT_RIB_PRO_TITLE)}
                  hint_text={trans(DOCUMENT_RIB_PRO_HINT)}
                  type={"rib"}
                />
              </section>
            </div>
            }
            {!isPersonneMorale &&
            <div className="fr-col-12">
              <section className="pr-form-section fr-p-4w">
                <Document
                  liasseDocumentaireIri={user.personnePhysique.liasseDocumentaire}
                  label={trans(DOCUMENT_RIB_TITLE)}
                  hint_text={trans(DOCUMENT_RIB_HINT)}
                  type={"rib"}
                />
              </section>
            </div>
            }
            <div className="fr-col-12">
              <section className="pr-form-section fr-p-4w">
                <Document
                  liasseDocumentaireIri={brisPorte.liasseDocumentaire}
                  label={trans(DOCUMENT_TITRE_PROPRIETE_TITLE)}
                  hint_text={trans(DOCUMENT_TITRE_PROPRIETE_HINT)}
                  type={"titre_propriete"}
                />
              </section>
            </div>
            {!isPersonneMorale &&
            <div className="fr-col-12">
              <section className="pr-form-section fr-p-4w">
                <Document
                  liasseDocumentaireIri={brisPorte.liasseDocumentaire}
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
                  <Button onClick={incrementStep}>{trans(GLOBAL_STEP_NEXT)}</Button>
                </li>
                <li>
                  <Button onClick={decrementStep} priority="secondary">{trans(GLOBAL_STEP_PREVIOUS)}</Button>
                </li>
              </ul>
            </div>
          </div>
        </>
        }
        {(step===3) &&
        <>
          <RecapitulatifBrisPorte
            brisPorte={_brisPorte}
            user={_user}
            gotoFirstSection={gotoFirstSection}
            gotoSecondSection={gotoSecondSection}
            gotoThirdSection={gotoThirdSection}
          />
          <div className="fr-col-12">
            <ul className="fr-btns-group fr-btns-group--inline-sm">
              <li>
                <Button
                  linkProps={{
                    href: Routing.generate('app_requerant_update_statut_to_constitue',{id:brisPorte.id})
                  }}
                >
                {trans(BRIS_PORTE_EDIT_UPDATE_CONSTITUE)}
                </Button>
              </li>
              <li>
                <Button onClick={decrementStep} priority="secondary">{trans(GLOBAL_STEP_PREVIOUS)}</Button>
              </li>
            </ul>
          </div>
        </>
        }
      <div className="fr-col-12">
        <Br space={2}/>
      </div>

  </>
  );
}

export default BrisPortePanel;
