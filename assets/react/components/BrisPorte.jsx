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
  const [currentStep,setCurrentStep]=useState(1)
  const [title,setTitle]=useState("");
  const [nextTitle,setNextTitle]=useState("");

  function getCurrentStep() { return step+1; }

  useEffect(() => {
    setCurrentStep(step+1);
    setTitle(sections[step]);
    if(sections[step+1])
      setNextTitle(sections[step+1]);
  },[step]);

  return (
    <div className="fr-grid-row">
      <div className="fr-col-12">
        <Stepper
          currentStep={currentStep}
          nextTitle={nextTitle}
          stepCount={sections.length}
          title={title}
        />
      </div>
      <div className="fr-col-12">
        <User
          user={user}
        />
      </div>
    </div>
  );
}

export default BrisPorte;
