import React from 'react';
import ReadOnlyInput from '../ReadOnlyInput';
import { fr } from "@codegouvfr/react-dsfr";
import { trans,
  PERSONNE_MORALE_FIELD_SIREN_SIRET,
  PERSONNE_MORALE_FIELD_RAISON_SOCIALE,
  GLOBAL_ERROR_EMPTY_FIELD
} from '../../../translator';

const PersonneMoraleView = ({personneMorale}) => {

  return (
    <div className="fr-grid-row">
      <div className="fr-col-12">
        <h5>Identité société</h5>
      </div>
      <div className="fr-col-5">
        <ReadOnlyInput
          label={trans(PERSONNE_MORALE_FIELD_SIREN_SIRET)}
          value={personneMorale.sirenSiret}
        />
      </div>
      <div className="fr-col-1">
      </div>
      <div className="fr-col-6">
        <ReadOnlyInput
          label={trans(PERSONNE_MORALE_FIELD_RAISON_SOCIALE)}
          value={personneMorale.raisonSociale}
        />
      </div>
    </div>
  );
}

export default PersonneMoraleView;
