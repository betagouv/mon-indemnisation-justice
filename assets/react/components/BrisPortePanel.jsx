import React, {useState,useEffect} from 'react';
import { fr } from "@codegouvfr/react-dsfr";
import { Table } from "@codegouvfr/react-dsfr/Table";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { Br } from "../utils/fundamental";
import { trans, BRIS_PORTE_SECTION,USER_SECTION,
  GLOBAL_STEP_NEXT,GLOBAL_STEP_PREVIOUS,
  BRIS_PORTE_EDIT_UPDATE_CONSTITUE,
  GLOBAL_INFORMATIONS_REQUIREMENT,
  BRIS_PORTE_PJ_SECTION
} from '../../translator';
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import BrisPorte from './BrisPorte';
import User from './User';

const BrisPortePanel = function({id,user,brisPorte}) {

  const sections = [
    trans(USER_SECTION),
    trans(BRIS_PORTE_SECTION),
    trans(BRIS_PORTE_PJ_SECTION)
  ];
  const [step,setStep]=useState(0);
  const [title,setTitle]=useState("");
  const [nextTitle,setNextTitle]=useState("");
  function incrementStep() { setStep(step+1); }
  function decrementStep() { setStep(step-1); }
  function getCurrentStep() { return step+1; }

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
            <User user={user} id={id}/>
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
          <div className="fr-col-9">
            <Button onClick={decrementStep}>{trans(GLOBAL_STEP_PREVIOUS)}</Button>
          </div>
          <div className="fr-col-3">
            <Button
              linkProps={{
                href: Routing.generate('app_requerant_update_statut_to_constitue',{id:brisPorte.id})
              }}
            >
            {trans(BRIS_PORTE_EDIT_UPDATE_CONSTITUE)}
            </Button>
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
