import React, {useState,useEffect} from 'react';
import { Select } from "@codegouvfr/react-dsfr/Select";
import ReadOnlyInput from '../ReadOnlyInput';
import { Input } from "@codegouvfr/react-dsfr/Input";
import { trans, ADRESSE_FIELD_LIGNE1,
  ADRESSE_FIELD_CODE_POSTAL,
  ADRESSE_FIELD_LOCALITE
} from '../../../translator';

const AdresseView = ({adresse}) => {

  return (
    <div className="fr-grid-row">
      <div className="fr-col-12">
        <ReadOnlyInput
          label={trans(ADRESSE_FIELD_LIGNE1)}
          value={adresse.ligne1}
        />
      </div>
      <div className="fr-col-3">
        <ReadOnlyInput
          label={trans(ADRESSE_FIELD_CODE_POSTAL)}
          value={adresse.codePostal}
        />
      </div>
      <div className="fr-col-1">
      </div>
      <div className="fr-col-8">
        <ReadOnlyInput
          label={trans(ADRESSE_FIELD_LOCALITE)}
          value={adresse.localite}
        />
      </div>
    </div>
  );
}

export default AdresseView;
