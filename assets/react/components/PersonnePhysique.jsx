import React, {useState,useEffect} from 'react';
import axios from 'axios';
import PropTypes from 'prop-types';
import Civilite from './Civilite';
import { getStateOnEmpty } from '../utils/check_state';
import SecuriteSociale from './SecuriteSociale';
import { fr } from "@codegouvfr/react-dsfr";
import { Table } from "@codegouvfr/react-dsfr/Table";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { Select } from "@codegouvfr/react-dsfr/Select";
import { trans,
  BRIS_PORTE_SECTION,USER_SECTION,USER_H5, GLOBAL_OPTIONAL,
  USER_FIELD_NOM,USER_FIELD_NOM_NAISSANCE,
  USER_FIELD_PRENOMS,USER_FIELD_DATE_NAISSANCE,
  USER_FIELD_LIEU_NAISSANCE,USER_FIELD_PAYS_NAISSANCE,
  GLOBAL_ERROR_EMPTY_FIELD, LOGIN_EMAIL, USER_FIELD_PRENOM1,
   USER_FIELD_PRENOM2, USER_FIELD_PRENOM3
} from '../../translator';
import { Input } from "@codegouvfr/react-dsfr/Input";
import { castDate } from '../utils/cast';

const PersonnePhysique = function({personnePhysique}) {

  const [numeroSS, setNumeroSS]=useState(personnePhysique.numeroSecuriteSociale??"");
  const [codeSS, setCodeSS]=useState(personnePhysique.codeSecuriteSociale??"");
  const [civilite,setCivilite]=useState(personnePhysique.civilite??"");
  const [nom, setNom]=useState(personnePhysique.nom??"");
  const [prenom1, setPrenom1]=useState(personnePhysique.prenom1??"");
  const [prenom2, setPrenom2]=useState(personnePhysique.prenom2??"");
  const [prenom3, setPrenom3]=useState(personnePhysique.prenom3??"");
  const [nomNaissance, setNomNaissance]=useState(personnePhysique.nomNaissance??"");
  const [dateNaissance, setDateNaissance]=useState(castDate(personnePhysique.dateNaissance));
  const [communeNaissance, setCommuneNaissance]=useState(personnePhysique.communeNaissance??"");

  const [stateNom, setStateNom]=useState(getStateOnEmpty(personnePhysique.nom));
  const [statePrenom1, setStatePrenom1]=useState(getStateOnEmpty(personnePhysique.prenom1));
  const [recordActived, setRecordActived]=useState(false);

  function mustBeRecorded() {
    const test =
      (numeroSS !== personnePhysique.numeroSecuriteSociale) ||
      (codeSS !== personnePhysique.codeSecuriteSociale) ||
      (civilite !== personnePhysique.civilite) ||
      (nom !== personnePhysique.nom) ||
      (prenom1 !== personnePhysique.prenom1) ||
      (prenom2 !== personnePhysique.prenom2) ||
      (prenom3 !== personnePhysique.prenom3) ||
      (nomNaissance !== personnePhysique.nomNaissance) ||
      (dateNaissance !== personnePhysique.dateNaissance) ||
      (communeNaissance !== personnePhysique.communeNaissance) ||
      (true === recordActived)
    ;
    setRecordActived(test);
    return test;
  }
  useEffect(() => {
    setStateNom(getStateOnEmpty(nom));
    setStatePrenom1(getStateOnEmpty(prenom1));
  },[nom,nomNaissance]);

  useEffect(() => {

    if(false === mustBeRecorded())
      return;

    const url =Routing.generate('_api_personne_physique_patch',{id:personnePhysique.id});
    const data = { nom:nom, nomNaissance: nomNaissance,
      prenom1: prenom1, prenom2: prenom2, prenom3: prenom3, codeSecuriteSociale: codeSS,
      communeNaissance: communeNaissance, numeroSecuriteSociale: numeroSS
    };
    if(civilite) { data['civilite']=civilite }
    fetch(url, {
      method: 'PATCH',
      headers: {'Content-Type': 'application/merge-patch+json'},
      body: JSON.stringify(data)
    })
    .then((response) => response.json())
    .then((data) => console.log('backup'))
    ;
  },[codeSS, nom, nomNaissance, numeroSS, prenom1, prenom2, prenom3,
    civilite, dateNaissance, communeNaissance, numeroSS
  ]);

  return (
    <>
      <div className="fr-grid-row">
        <div className="fr-col-12">
          <h5>{trans(USER_H5)}</h5>
        </div>
        <div className="fr-col-2 fr-pr-md-1w">
          <Civilite civilite={civilite} setCivilite={setCivilite}/>
        </div>
        <div className="fr-col-5 fr-pr-md-1w">
          <Input
            label={trans(USER_FIELD_NOM)}
            state={stateNom}
            stateRelatedMessage={trans(GLOBAL_ERROR_EMPTY_FIELD)}
            nativeInputProps={{name: 'nom', value: nom, onChange: ev => setNom(ev.target.value)}}
          />
        </div>
        <div className="fr-col-5">
        <Input
          label={trans(USER_FIELD_NOM_NAISSANCE)+" "+trans(GLOBAL_OPTIONAL)}
          nativeInputProps={{name: 'nomNaissance', value: nomNaissance, onChange: ev => setNomNaissance(ev.target.value)}}
        />
        </div>
        <div className="fr-col-12">
          <Input
            label={trans(USER_FIELD_PRENOMS)}
            state={statePrenom1}
            stateRelatedMessage={trans(GLOBAL_ERROR_EMPTY_FIELD)}
            nativeInputProps={{placeholder: trans(USER_FIELD_PRENOM1), name: 'prenom1', value: prenom1, onChange: ev => setPrenom1(ev.target.value)}}
          />
        </div>
        <div className="fr-col-2 fr-pr-md-1w">
          <Input
            label={trans(USER_FIELD_DATE_NAISSANCE)}
            nativeInputProps={{
              type: 'date',value: dateNaissance, onChange: ev=>setDateNaissance(ev.target.value)
            }}
          />
        </div>
        <div className="fr-col-5">
          <Input
            label={trans(USER_FIELD_LIEU_NAISSANCE)}
            nativeInputProps={{
              value: communeNaissance, onChange: ev => setCommuneNaissance(ev.target.value)
            }}
          />
        </div>
        <div className="fr-col-5">
        </div>
        <div className="fr-col-7">
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

PersonnePhysique.propTypes = {
  nom: PropTypes.string
}

export default PersonnePhysique;
