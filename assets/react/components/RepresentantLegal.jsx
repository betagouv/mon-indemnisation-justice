import React, {useState,useEffect,useRef} from 'react';
import axios from 'axios';
import PropTypes from 'prop-types';
import Civilite from './Civilite';
import { getStateOnEmpty } from '../utils/check_state';
import SecuriteSociale from './SecuriteSociale';
import { fr } from "@codegouvfr/react-dsfr";
import { Table } from "@codegouvfr/react-dsfr/Table";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { Select } from "@codegouvfr/react-dsfr/Select";
import { castDate,formatUrl,formatDate } from '../utils/cast';
import { trans,
  BRIS_PORTE_SECTION,USER_SECTION,
  GLOBAL_OPTIONAL,USER_FIELD_EMAIL_PRO,USER_FIELD_TELEPHONE_PRO,
  USER_FIELD_NOM,USER_FIELD_NOM_NAISSANCE,
  USER_FIELD_PRENOMS,USER_FIELD_DATE_NAISSANCE,
  USER_FIELD_LIEU_NAISSANCE,USER_FIELD_PAYS_NAISSANCE,
  GLOBAL_ERROR_EMPTY_FIELD, LOGIN_EMAIL, USER_FIELD_PRENOM1,
  REPRESENTANT_LEGAL_TITLE
} from '../../translator';
import { Input } from "@codegouvfr/react-dsfr/Input";

const RepresentantLegal = function({personnePhysique}) {

  const [numeroSS, setNumeroSS]=useState(personnePhysique.numeroSecuriteSociale);
  const [codeSS, setCodeSS]=useState(personnePhysique.codeSecuriteSociale);
  const [civilite,setCivilite]=useState(personnePhysique.civilite??"");
  const [nom, setNom]=useState(personnePhysique.nom??"");
  const [prenom1, setPrenom1]=useState(personnePhysique.prenom1??"");
  const [nomNaissance, setNomNaissance]=useState(personnePhysique.nomNaissance??"");
  const [dateNaissance, setDateNaissance]=useState(castDate(personnePhysique.dateNaissance??""));
  const [communeNaissance, setCommuneNaissance]=useState(personnePhysique.communeNaissance??"");
  const [email, setEmail]=useState(personnePhysique.email??"");
  const [telephone, setTelephone]=useState(personnePhysique.telephone??"");

  const [stateNom, setStateNom]=useState(getStateOnEmpty(personnePhysique.nomNaissance));
  const [statePrenom1, setStatePrenom1]=useState(getStateOnEmpty(personnePhysique.prenom1));
  const [recordActived, setRecordActived]=useState(false);

  var keyUpTimer = useRef(null);
  const KEY_UP_TIMER_DELAY = 1000;

  useEffect(() => {
    setStateNom(getStateOnEmpty(nomNaissance));
    setStatePrenom1(getStateOnEmpty(prenom1));
  },[prenom1,nomNaissance]);

  function mustBeRecorded() {
    const test =
      (numeroSS !== personnePhysique.numeroSecuriteSociale) ||
      (codeSS !== personnePhysique.codeSecuriteSociale) ||
      (civilite !== personnePhysique.civilite) ||
      (nom !== personnePhysique.nom) ||
      (email !== personnePhysique.email) ||
      (prenom1 !== personnePhysique.prenom1) ||
      (telephone !== personnePhysique.telephone) ||
      (nomNaissance !== personnePhysique.nomNaissance) ||
      (dateNaissance !== castDate(personnePhysique.dateNaissance??'')) ||
      (communeNaissance !== personnePhysique.communeNaissance) ||
      (true === recordActived)
    ;

    setRecordActived(test);
    return test;
  }

  useEffect(() => {

    if(false === mustBeRecorded())
      return;

    const url =Routing.generate('_api_personne_physique_patch',{id:personnePhysique.id});
    const data = { nom:nom, nomNaissance: nomNaissance,
      prenom1: prenom1, codeSecuriteSociale: codeSS, email: email,
      communeNaissance: communeNaissance, numeroSecuriteSociale: numeroSS,
      telephone: telephone, dateNaissance: formatDate(dateNaissance)
    };

    if(civilite) { data['civilite']=civilite }
    clearTimeout(keyUpTimer.current);
    keyUpTimer.current = setTimeout(() => {
      fetch(url, {
        method: 'PATCH',
        headers: {'Content-Type': 'application/merge-patch+json'},
        body: JSON.stringify(data)
      })
      .then((response) => response.json())
      .then((data) => console.log('backup'))
      ;
    }, KEY_UP_TIMER_DELAY);
  },[codeSS, nom, nomNaissance, numeroSS, prenom1,email,telephone,
    civilite, dateNaissance, communeNaissance, numeroSS
  ]);

  return (
    <>
      <h3>{trans(REPRESENTANT_LEGAL_TITLE)}</h3>
      <div className="fr-grid-row fr-grid-row--gutters">
        <div className="fr-col-2">
          <Civilite civilite={civilite} setCivilite={setCivilite}/>
        </div>
        <div className="fr-col-10">
          <Input
            label={trans(USER_FIELD_PRENOMS)}
            state={statePrenom1}
            stateRelatedMessage={trans(GLOBAL_ERROR_EMPTY_FIELD)}
            nativeInputProps={{placeholder: trans(USER_FIELD_PRENOM1), name: 'prenom1', value: prenom1, onChange: ev => setPrenom1(ev.target.value)}}
          />
        </div>
        <div className="fr-col-6">
          <Input
            label={trans(USER_FIELD_NOM_NAISSANCE)}
            state={stateNom}
            stateRelatedMessage={trans(GLOBAL_ERROR_EMPTY_FIELD)}
            nativeInputProps={{name: 'nomNaissance', value: nomNaissance, onChange: ev => setNomNaissance(ev.target.value)}}
          />
        </div>
        <div className="fr-col-6">
          <Input
            label={trans(USER_FIELD_NOM)+' '+trans(GLOBAL_OPTIONAL)}
            nativeInputProps={{value: nom, onChange: ev => setNom(ev.target.value)}}
          />
        </div>
        <div className="fr-col-6">
          <Input
            label={trans(USER_FIELD_EMAIL_PRO)}
            nativeInputProps={{value: email, onChange: ev => setEmail(ev.target.value)}}
          />
        </div>
        <div className="fr-col-6">
          <Input
            label={trans(USER_FIELD_TELEPHONE_PRO)}
            nativeInputProps={{value: telephone, onChange: ev => setTelephone(ev.target.value)}}
          />
        </div>
      </div>
    </>
  );
}

RepresentantLegal.propTypes = {
  nom: PropTypes.string
}

export default RepresentantLegal;
