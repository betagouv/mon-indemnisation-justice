import React, {useState,useEffect,useRef} from 'react';
import PropTypes from 'prop-types';
import Civilite from './Civilite';
import { getStateOnEmpty } from '../utils/check_state';
import SecuriteSociale from './SecuriteSociale';
import { Input } from "@codegouvfr/react-dsfr/Input";
import { castDate } from '../utils/cast';

const PersonnePhysique = function({personnePhysique}) {

  const [numeroSS, setNumeroSS]=useState(personnePhysique.numeroSecuriteSociale??"");
  const [codeSS, setCodeSS]=useState(personnePhysique.codeSecuriteSociale??"");
  const [civilite,setCivilite]=useState(personnePhysique.civilite??"");
  const [nom, setNom]=useState(personnePhysique.nom??"");
  const [prenom1, setPrenom1]=useState(personnePhysique.prenom1??"");
  const [nomNaissance, setNomNaissance]=useState(personnePhysique.nomNaissance??"");
  const [dateNaissance, setDateNaissance]=useState(castDate(personnePhysique.dateNaissance));
  const [communeNaissance, setCommuneNaissance]=useState(personnePhysique.communeNaissance??"");

  const [stateNom, setStateNom]=useState(getStateOnEmpty(personnePhysique.nomNaissance));
  const [statePrenom1, setStatePrenom1]=useState(getStateOnEmpty(personnePhysique.prenom1));
  const [recordActived, setRecordActived]=useState(false);

  var keyUpTimer = useRef(null);
  const KEY_UP_TIMER_DELAY = 1000;

  function mustBeRecorded() {
    const test =
      (numeroSS !== personnePhysique.numeroSecuriteSociale) ||
      (codeSS !== personnePhysique.codeSecuriteSociale) ||
      (civilite !== personnePhysique.civilite) ||
      (nom !== personnePhysique.nom) ||
      (prenom1 !== personnePhysique.prenom1) ||
      (nomNaissance !== personnePhysique.nomNaissance) ||
      (dateNaissance !== personnePhysique.dateNaissance) ||
      (communeNaissance !== personnePhysique.communeNaissance) ||
      (true === recordActived)
    ;
    setRecordActived(test);
    return test;
  }
  useEffect(() => {
    setStateNom(getStateOnEmpty(nomNaissance));
    setStatePrenom1(getStateOnEmpty(prenom1));
  },[prenom1,nomNaissance]);

  useEffect(() => {

    if(false === mustBeRecorded())
      return;
    const url = Routing.generate('_api_personne_physique_patch',{id:personnePhysique.id});
    const data = { nom:nom, nomNaissance: nomNaissance,
      prenom1: prenom1, codeSecuriteSociale: codeSS,
      communeNaissance: communeNaissance, numeroSecuriteSociale: numeroSS
    };
    if(civilite) { data['civilite']=civilite }
    if(dateNaissance) { data['dateNaissance']=dateNaissance }
    clearTimeout(keyUpTimer.current);
    keyUpTimer.current = setTimeout(() => {
      fetch(url, {
        method: 'PATCH',
        headers: {'Content-Type': 'application/merge-patch+json'},
        body: JSON.stringify(data)
      })
        .then((response) => response.json())
        .then((data) => {})
      ;
    }, KEY_UP_TIMER_DELAY);
  },[codeSS, nom, nomNaissance, numeroSS, prenom1,
    civilite, dateNaissance, communeNaissance, numeroSS
  ]);

  return (
    <>
      <h3>Votre identité</h3>
      <div className="fr-grid-row fr-grid-row--gutters">
        <div className="fr-col-lg-3 fr-col-4">
          <Civilite civilite={civilite} setCivilite={setCivilite}/>
        </div>
        <div className="fr-col-lg-9 fr-col-8">
          <Input
            label="Prénom(s)"
            state={statePrenom1}
            stateRelatedMessage="Le champs est obligatoire"
            nativeInputProps={{
              placeholder: "Prénom(s)",
              value: prenom1,
              onChange: ev => setPrenom1(ev.target.value),
              maxLength: 255
            }}
          />
        </div>
        <div className="fr-col-lg-6 fr-col-12">
          <Input
            label="Nom de naissance"
            state={stateNom}
            stateRelatedMessage="Le champs est obligatoire"
            nativeInputProps={{
              value: nomNaissance,
              onChange: ev => setNomNaissance(ev.target.value),
              maxLength: 255
            }}
          />
        </div>
        <div className="fr-col-lg-6 fr-col-12">
          <Input
            label="Nom d'usage"
            nativeInputProps={{
              value: nom,
              onChange: ev => setNom(ev.target.value),
              maxLength: 255
            }}
          />
        </div>
        <div className="fr-col-lg-3 fr-col-12">
          <Input
            label="Date de naissance"
            nativeInputProps={{
              type: 'date',
              value: dateNaissance,
              onChange: ev=>setDateNaissance(ev.target.value)
            }}
          />
        </div>
        <div className="fr-col-lg-9 fr-col-12">
          <Input
            label="Ville de naissance"
            nativeInputProps={{
              value: communeNaissance,
              onChange: ev => setCommuneNaissance(ev.target.value),
              maxLength: 255
            }}
          />
        </div>
        <div className="fr-col-lg-6 fr-col-12">
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
