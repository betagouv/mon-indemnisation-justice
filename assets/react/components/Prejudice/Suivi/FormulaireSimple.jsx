import React,{useState,} from 'react';
import { Hidden, Submit } from '../../../utils/fundamental';

import { Input } from "@codegouvfr/react-dsfr/Input";

const FormulaireSimple = ({csrfToken,raccourci="",errorMessage=""}) => {

  const RACCOURCI_LENGTH=8;
  const [_raccourci,setRaccourci]=useState(raccourci)
  const handleContinue = (event) => {
    if(_raccourci.length !== RACCOURCI_LENGTH) {
      alert("Le code doit faire %length% caractères".replace("%length%",RACCOURCI_LENGTH));
      event.preventDefault();
    }
  }

  return (
    <form onSubmit={handleContinue} method="POST" name="form">
      <Hidden name="_csrf_token" value={csrfToken} />
      <div class="fr-grid-row">
        <div className="fr-col-4">&nbsp;</div>
        <div className="fr-col-4">
          <Input
            label="Code suivi"
            nativeInputProps={{
              name:"raccourci",
              value: _raccourci,
              onChange: ev => setRaccourci(ev.target.value),
              maxLength: RACCOURCI_LENGTH
            }}
            addon={<Submit label={"Consulter"} />}
          />
        </div>
        <div className="fr-col-4">&nbsp;</div>
      </div>
    </form>
  );
}

export default FormulaireSimple;
