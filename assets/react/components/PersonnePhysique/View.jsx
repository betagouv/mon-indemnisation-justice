import React, {useState,useEffect} from 'react';
import axios from 'axios';
import PropTypes from 'prop-types';
import { getStateOnEmpty } from '../../utils/check_state';
import SecuriteSociale from '../SecuriteSociale';
import { fr } from "@codegouvfr/react-dsfr";
import { Table } from "@codegouvfr/react-dsfr/Table";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { Select } from "@codegouvfr/react-dsfr/Select";
import { trans,
  BRIS_PORTE_SECTION,USER_SECTION,
  USER_FIELD_NOM,USER_FIELD_NOM_NAISSANCE,
  USER_FIELD_PRENOMS,USER_FIELD_DATE_NAISSANCE,
  USER_FIELD_LIEU_NAISSANCE,USER_FIELD_PAYS_NAISSANCE,
  GLOBAL_ERROR_EMPTY_FIELD, LOGIN_EMAIL, USER_FIELD_PRENOM1,
   USER_FIELD_PRENOM2, USER_FIELD_PRENOM3
} from '../../../translator';
import { Input } from "@codegouvfr/react-dsfr/Input";
import { castDate } from '../../utils/cast';

const PersonnePhysiqueView = function({personnePhysique}) {

  const [numeroSS, setNumeroSS]=useState(personnePhysique.numeroSecuriteSociale);
  const [codeSS, setCodeSS]=useState(personnePhysique.codeSecuriteSociale);
  const [civilite,setCivilite]=useState(personnePhysique.civilite??"");
  const [nom, setNom]=useState(personnePhysique.nom??"");
  const [prenom1, setPrenom1]=useState(personnePhysique.prenom1??"");
  const [prenom2, setPrenom2]=useState(personnePhysique.prenom2??"");
  const [prenom3, setPrenom3]=useState(personnePhysique.prenom3??"");
  const [nomNaissance, setNomNaissance]=useState(personnePhysique.nomNaissance??"");
  const [dateNaissance, setDateNaissance]=useState(castDate(personnePhysique.dateNaissance));
  const [communeNaissance, setCommuneNaissance]=useState(personnePhysique.communeNaissance??"");

  return (
    <>
      <div className="fr-grid-row">
        <div className="fr-col-12">
          <h5>Identité de la personne</h5>
        </div>
        <div className="fr-col-3">
          <CiviliteView civilite={civilite} setCivilite={setCivilite}/>
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-3">
          <Input
            label={trans(USER_FIELD_NOM)}
            stateRelatedMessage={trans(GLOBAL_ERROR_EMPTY_FIELD)}
            nativeInputProps={{name: 'nom', value: nom, onChange: ev => setNom(ev.target.value)}}
          />
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-3">
        <Input
          label={trans(USER_FIELD_NOM_NAISSANCE)}
          nativeInputProps={{name: 'nomNaissance', value: nomNaissance, onChange: ev => setNomNaissance(ev.target.value)}}
        />
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-3">
          <Input
            label={trans(LOGIN_EMAIL)}
            nativeInputProps={{value: personnePhysique.email}}
            disabled
          />
        </div>
      </div>
      <div className="fr-grid-row">
        <div className="fr-col-3">
          <Input
            label={trans(USER_FIELD_PRENOMS)}
            stateRelatedMessage={trans(GLOBAL_ERROR_EMPTY_FIELD)}
            nativeInputProps={{placeholder: trans(USER_FIELD_PRENOM1), name: 'prenom1', value: prenom1, onChange: ev => setPrenom1(ev.target.value)}}
          />
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-3">
          <Input
            label="&nbsp;"
            nativeInputProps={{placeholder: trans(USER_FIELD_PRENOM2), name: 'prenom2', value: prenom2, onChange: ev => setPrenom2(ev.target.value)}}
          />
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-3">
          <Input
            label="&nbsp;"
            nativeInputProps={{placeholder: trans(USER_FIELD_PRENOM3), name: 'prenom3', value: prenom3, onChange: ev => setPrenom3(ev.target.value)}}
          />
        </div>
        <div className="fr-col-1">
        </div>
      </div>
      <div className="fr-grid-row">
        <div className="fr-col-3">
          <Input
            label={trans(USER_FIELD_DATE_NAISSANCE)}
            nativeInputProps={{
              type: 'date',value: dateNaissance, onChange: ev=>setDateNaissance(ev.target.value)
            }}
          />
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-3">
          <Input
            label={trans(USER_FIELD_LIEU_NAISSANCE)}
            nativeInputProps={{
              value: communeNaissance, onChange: ev => setCommuneNaissance(ev.target.value)
            }}
          />
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-6">
          <SecuriteSociale
            codeSS={codeSS}
            numeroSS={numeroSS}
            setCodeSS={setCodeSS}
            setNumeroSS={setNumeroSS}
          />
        </div>
      </div>
    </>
  );
}

PersonnePhysiqueView.propTypes = {
  nom: PropTypes.string
}

export default PersonnePhysiqueView;