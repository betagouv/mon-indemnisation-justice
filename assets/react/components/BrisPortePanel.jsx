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
  const gotoFirstSection= () => setStep(0);
  const gotoSecondSection= () => setStep(1);
  const [step,setStep]=useState(0);
  const [title,setTitle]=useState("");
  const [nextTitle,setNextTitle]=useState("");
  const [isPersonneMorale,setIsPersonneMorale]=useState(user.isPersonneMorale);
  function incrementStep() { setStep(step+1); }
  function decrementStep() { setStep(step-1); }
  function getCurrentStep() { return step+1; }

  const toggleIsPersonneMorale = () => setIsPersonneMorale(!isPersonneMorale);

  useEffect(() => {
    setTitle(sections[step]);
    if(sections[step+1])
      setNextTitle(sections[step+1]);
  },[step]);

  return (
    <div className="fr-grid-row">
      <div className="fr-col-12">
        <Stepper
          currentStep={getCurrentStep()}
          nextTitle={nextTitle}
          stepCount={sections.length}
          title={title}
        />
        {(step===0) &&
        <>
          <div className="fr-col-12">
          {trans(GLOBAL_INFORMATIONS_REQUIREMENT)}
          </div>
          <div className="fr-col-12">
            <User user={user} id={id} toggleIsPersonneMorale={toggleIsPersonneMorale}/>
          </div>
          <div className="fr-col-12">
            <Br space={2}/>
          </div>
          <div className="fr-col-12">
            <Button onClick={incrementStep}>{trans(GLOBAL_STEP_NEXT)}</Button>
          </div>
        </>
        }
        {(step===1) &&
        <>
          <div className="fr-col-12">
            <BrisPorte brisPorte={brisPorte} />
          </div>
          <div className="fr-col-12">
            <Br space={2}/>
          </div>
          <div className="fr-col-12">
            <div className="fr-grid-row">
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
          </div>
        </>
        }
        {(step===2) &&
        <>
          <div className="fr-col-12">
            <Document
              liasseDocumentaireIri={brisPorte.liasseDocumentaire}
              label={trans(DOCUMENT_ATTESTATION_INFORMATION_TITLE)}
              type={"attestation_information"}
            />
            <Br space={2} />
          </div>
          <div className="fr-col-12">
            <Document
              liasseDocumentaireIri={brisPorte.liasseDocumentaire}
              label={trans(DOCUMENT_PHOTO_BRIS_PORTE_TITLE)}
              hint_text={trans(DOCUMENT_PHOTO_BRIS_PORTE_HINT)}
              type={"photo_prejudice"}
            />
            <Br space={2} />
          </div>
          {!isPersonneMorale &&
          <div className="fr-col-12">
            <Document
              liasseDocumentaireIri={user.personnePhysique.liasseDocumentaire}
              label={trans(DOCUMENT_PIECE_IDENTITE_TITLE)}
              hint_text={trans(DOCUMENT_PIECE_IDENTITE_HINT)}
              type={"carte_identite"}
            />
            <Br space={2} />
          </div>
          }
          <div className="fr-col-12">
            <Document
              liasseDocumentaireIri={brisPorte.liasseDocumentaire}
              label={trans(DOCUMENT_FACTURE_TITLE)}
              hint_text={trans(DOCUMENT_FACTURE_HINT)}
              type={"preuve_paiement_facture"}
            />
            <Br space={2} />
          </div>
          {isPersonneMorale &&
          <div className="fr-col-12">
            <Document
              liasseDocumentaireIri={user.personneMorale.liasseDocumentaire}
              label={trans(DOCUMENT_RIB_PRO_TITLE)}
              hint_text={trans(DOCUMENT_RIB_PRO_HINT)}
              type={"rib"}
            />
            <Br space={2} />
          </div>
          }
          {!isPersonneMorale &&
          <div className="fr-col-12">
            <Document
              liasseDocumentaireIri={user.personnePhysique.liasseDocumentaire}
              label={trans(DOCUMENT_RIB_TITLE)}
              hint_text={trans(DOCUMENT_RIB_HINT)}
              type={"rib"}
            />
            <Br space={2} />
          </div>
          }
          <div className="fr-col-12">
            <Document
              liasseDocumentaireIri={brisPorte.liasseDocumentaire}
              label={trans(DOCUMENT_TITRE_PROPRIETE_TITLE)}
              hint_text={trans(DOCUMENT_TITRE_PROPRIETE_HINT)}
              type={"titre_propriete"}
            />
            <Br space={2} />
          </div>
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
        {(step===3) &&
        <>
          <div className="fr-col-12">
            <RecapitulatifBrisPorte
              brisPorte={brisPorte}
              user={user}
              gotoFirstSection={gotoFirstSection}
              gotoSecondSection={gotoSecondSection}
            />
          </div>
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
      </div>
      <div className="fr-col-12">
        <Br space={2}/>
      </div>
    </div>
  );
}

export default BrisPortePanel;
