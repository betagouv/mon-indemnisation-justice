import React, {useState,useEffect,useRef} from 'react';
import PropTypes from 'prop-types';
import Civilite from '../Civilite';
import { getStateOnEmpty } from '../../utils/check_state';
import Requerant from '../Requerant';
import { Input } from "@codegouvfr/react-dsfr/Input";

const FormulaireReceveur = function({personnePhysique}) {
  const [numeroSS, setNumeroSS]=useState(personnePhysique.numeroSecuriteSociale??"");
  const [codeSS, setCodeSS]=useState(personnePhysique.codeSecuriteSociale??"");
  const [civilite,setCivilite]=useState(personnePhysique.civilite??"");
  const [nom, setNom]=useState(personnePhysique.nom??"");
  const [prenom1, setPrenom1]=useState(personnePhysique.prenom1??"");
  const [nomNaissance, setNomNaissance]=useState(personnePhysique.nomNaissance??"");
  const [qualiteRequerant, setQualiteRequerant]=useState(personnePhysique.qualiteRequerant??"");
  const [precisionRequerant, setPrecisionRequerant]=useState(personnePhysique.precision??"");

  const [stateNom, setStateNom]=useState(getStateOnEmpty(personnePhysique.nomNaissance));
  const [statePrenom1, setStatePrenom1]=useState(getStateOnEmpty(personnePhysique.prenom1));
  const [recordActived, setRecordActived]=useState(false);
  const [loading, setLoading]=useState(false);

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
      (qualiteRequerant !== personnePhysique.qualiteRequerant) ||
      (precisionRequerant !== personnePhysique.precision) ||
      (true === recordActived)
    ;
    setRecordActived(test);
    return test;
  }

  useEffect(() => {
    if(true===loading)
      return;
    setLoading(true);
  },[]);

  useEffect(() => {
    setStateNom(getStateOnEmpty(nomNaissance));
    setStatePrenom1(getStateOnEmpty(prenom1));
  },[nomNaissance,prenom1]);

  useEffect(() => {
    if(false === loading)
      return;

    if(false === mustBeRecorded())
      return;

    const url =Routing.generate('_api_personne_physique_patch',{id:personnePhysique.id});
    const data = { nom:nom, nomNaissance: nomNaissance,
      prenom1: prenom1, codeSecuriteSociale: codeSS,
      numeroSecuriteSociale: numeroSS, precision: precisionRequerant
    };
    if(civilite) { data['civilite']=civilite }
    if(qualiteRequerant) { data['qualiteRequerant']=qualiteRequerant }

    clearTimeout(keyUpTimer.current);
    keyUpTimer.current = setTimeout(() => {
      fetch(url, {
        method: 'PATCH',
        redirect: 'error',
        headers: {'Content-Type': 'application/merge-patch+json'},
        body: JSON.stringify(data)
      })
      .then((response) => response.json())
      .then((data) => {})
      .catch(() => {})
      ;
    },KEY_UP_TIMER_DELAY);
  },[codeSS, nom, nomNaissance, numeroSS, prenom1,
    civilite, numeroSS, qualiteRequerant,
    precisionRequerant
  ]);

  return (
    <>
      <h3>Attestation remise par les force de l'ordre à</h3>
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
            label={"Nom d'usage"}
            nativeInputProps={{
              value: nom,
              onChange: ev => setNom(ev.target.value),
              maxLength: 255
            }}
          />
        </div>
        <div className="fr-col-12">
          <Requerant
            qualiteText={"Qualité"}
            precisionText={"Préciser sa qualité"}
            qualiteRequerant={qualiteRequerant}
            setQualiteRequerant={setQualiteRequerant}
            precisionRequerant={precisionRequerant}
            setPrecisionRequerant={setPrecisionRequerant}
          />
        </div>
      </div>
    </>
  );
}

FormulaireReceveur.propTypes = {
  nom: PropTypes.string
}

export default FormulaireReceveur;
