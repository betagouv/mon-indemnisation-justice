import React, {useState,useEffect} from 'react';
import axios from 'axios';
import PropTypes from 'prop-types';
import ReadOnlyInput from '../ReadOnlyInput';
import { getStateOnEmpty } from '../../utils/check_state';
import SecuriteSocialeView from '../SecuriteSociale/View';
import { fr } from "@codegouvfr/react-dsfr";
import { Table } from "@codegouvfr/react-dsfr/Table";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { Select } from "@codegouvfr/react-dsfr/Select";
import { normalizeDate,formatUrl,formatDate } from '../../utils/cast';
import { trans,
  BRIS_PORTE_SECTION,USER_SECTION,
  USER_FIELD_NOM,USER_FIELD_NOM_NAISSANCE,
  USER_FIELD_PRENOMS,USER_FIELD_DATE_NAISSANCE,
  USER_FIELD_LIEU_NAISSANCE,USER_FIELD_PAYS_NAISSANCE,
  GLOBAL_ERROR_EMPTY_FIELD, LOGIN_EMAIL, USER_FIELD_PRENOM1,
   USER_FIELD_PRENOM2, USER_FIELD_PRENOM3, USER_FIELD_CIVILITE
} from '../../../translator';
import { Input } from "@codegouvfr/react-dsfr/Input";

const RepresentantLegalView = function({personnePhysique}) {

  return (
    <>
      <div className="fr-grid-row">
        <div className="fr-col-12">
          <h5>Identité du représentant légal</h5>
        </div>
        <div className="fr-col-3">
          <ReadOnlyInput
            label={trans(USER_FIELD_CIVILITE)}
            value={personnePhysique.civilite.libelle}
          />
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-3">
          <ReadOnlyInput
            label={trans(USER_FIELD_NOM)}
            value={personnePhysique.nom}
          />
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-3">
        <ReadOnlyInput
          label={trans(USER_FIELD_NOM_NAISSANCE)}
          value={personnePhysique.nomNaissance}
        />
        </div>
      </div>
      <div className="fr-grid-row">
        <div className="fr-col-3">
          <ReadOnlyInput
            label={trans(USER_FIELD_PRENOMS)}
            value={personnePhysique.prenom1}
          />
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-3">
          <ReadOnlyInput
            label="&nbsp;"
            value={personnePhysique.prenom2}
          />
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-3">
          <ReadOnlyInput
            label="&nbsp;"
            value={personnePhysique.prenom3}
          />
        </div>
        <div className="fr-col-1">
        </div>
      </div>
      <div className="fr-grid-row">
        <div className="fr-col-3">
          <ReadOnlyInput
            label={trans(USER_FIELD_DATE_NAISSANCE)}
            value={normalizeDate(personnePhysique.dateNaissance)}
          />
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-3">
          <ReadOnlyInput
            label={trans(USER_FIELD_LIEU_NAISSANCE)}
            value={personnePhysique.communeNaissance}
          />
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-6">
          <SecuriteSocialeView
            codeSS={personnePhysique.codeSecuriteSociale}
            numeroSS={personnePhysique.numeroSecuriteSociale}
          />
        </div>
      </div>
    </>
  );
}

export default RepresentantLegalView;
