import React, {useState,useEffect} from 'react';
import axios from 'axios';
import PropTypes from 'prop-types';
import Referentiel from '../Referentiel';
import ReadOnlyInput from '../ReadOnlyInput';
import { getStateOnEmpty } from '../../utils/check_state';
import { fr } from "@codegouvfr/react-dsfr";
import { Table } from "@codegouvfr/react-dsfr/Table";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { Select } from "@codegouvfr/react-dsfr/Select";
import { trans,
  BRIS_PORTE_FIELD_QUALITE_REPRESENTANT,
  BRIS_PORTE_FIELD_PRECISION_REPRESENTANT
} from '../../../translator';
import { Input } from "@codegouvfr/react-dsfr/Input";

const RequerantView = function({
  qualiteRequerant,
  precisionRequerant
}) {
  console.log(qualiteRequerant);
  const CODE_QUALITE_REQUERANT_AUTRE = '4';

  return (
    <>
      <div className="fr-grid-row">
        <div className="fr-col-4">
          <ReadOnlyInput
            label={trans(BRIS_PORTE_FIELD_QUALITE_REPRESENTANT)}
            value={qualiteRequerant.libelle}
          />
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-7">
          <ReadOnlyInput
            label={trans(BRIS_PORTE_FIELD_PRECISION_REPRESENTANT)}
            value={precisionRequerant}
          />
        </div>
      </div>
    </>
  );
}

export default RequerantView;
