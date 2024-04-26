import React, {useState,useEffect} from 'react';
import { fr } from "@codegouvfr/react-dsfr";
import { Table } from "@codegouvfr/react-dsfr/Table";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { trans, BRIS_PORTE_SECTION,USER_SECTION } from '../../translator';
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import User from './User';

const BrisPorte = function({user}) {

  const sections = [trans(USER_SECTION),trans(BRIS_PORTE_SECTION)];
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
      </div>
      <div className="fr-col-12">
        {(step===0) &&
        <>
          <User user={user} />
          <Button onClick={incrementStep}>Next</Button>
        </>
        }
        {(step===1) &&
        <>
          <Button onClick={decrementStep}>Previous</Button>
        </>
        }
      </div>
    </div>
  );
}

export default BrisPorte;
