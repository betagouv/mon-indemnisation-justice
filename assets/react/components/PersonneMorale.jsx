import React, { useState,useEffect } from 'react';
import { getStateOnEmpty } from '../utils/check_state';
import { castNumber } from '../utils/cast';
import { Input } from "@codegouvfr/react-dsfr/Input";
import { fr } from "@codegouvfr/react-dsfr";
import { trans,
  PERSONNE_MORALE_TITLE,
  PERSONNE_MORALE_FIELD_SIREN_SIRET,
  PERSONNE_MORALE_FIELD_RAISON_SOCIALE,
  GLOBAL_ERROR_EMPTY_FIELD
} from '../../translator';

const PersonneMorale = ({personneMorale}) => {

  const [sirenSiret, setSirenSiret]=useState(personneMorale.sirenSiret??"");
  const [raisonSociale, setRaisonSociale]=useState(personneMorale.raisonSociale??"");

  const [stateRaisonSociale, setStateRaisonSociale]=useState(getStateOnEmpty(personneMorale.raisonSociale));
  const [stateSirenSiret, setStateSirenSiret]=useState(getStateOnEmpty(personneMorale.sirenSiret));
  const [loading, setLoading] = useState(false);
  useEffect(() => {
    setStateRaisonSociale(getStateOnEmpty(raisonSociale));
    setStateSirenSiret(getStateOnEmpty(sirenSiret));
  },[sirenSiret, raisonSociale]);

  useEffect(() => {
    if(!loading) {
      setLoading(true);
      return;
    }
    const url =Routing.generate('_api_personne_morale_patch',{id:personneMorale.id});
    const data = { sirenSiret: sirenSiret, raisonSociale: raisonSociale };

    fetch(url, {
      method: 'PATCH',
      headers: {'Content-Type': 'application/merge-patch+json'},
      body: JSON.stringify(data)
    })
    .then((response) => response.json())
    .then((data) => console.log('backup pm'))
    ;
  },[sirenSiret, raisonSociale]);
  return (
    <div className="fr-grid-row">
      <div className="fr-col-12">
        <h5>{trans(PERSONNE_MORALE_TITLE)}</h5>
      </div>
      <div className="fr-col-6 fr-pr-md-1w">
        <Input
          label={trans(PERSONNE_MORALE_FIELD_RAISON_SOCIALE)}
          state={stateRaisonSociale}
          stateRelatedMessage={trans(GLOBAL_ERROR_EMPTY_FIELD)}
          nativeInputProps={{
            name: 'raisonSociale',
            value: raisonSociale,
            onChange: ev => setRaisonSociale(ev.target.value),
            maxLength: 255
          }}
        />
      </div>
      <div className="fr-col-6">
        <Input
          label={trans(PERSONNE_MORALE_FIELD_SIREN_SIRET)}
          state={stateSirenSiret}
          stateRelatedMessage={trans(GLOBAL_ERROR_EMPTY_FIELD)}
          nativeInputProps={{
            name: 'sirenSiret',
            value: sirenSiret,
            onChange: ev => setSirenSiret(castNumber(ev.target.value)),
            maxLength: 255
          }}
        />
      </div>
    </div>
  );
}

export default PersonneMorale;
