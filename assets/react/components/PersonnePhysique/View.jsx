import React, {useState} from 'react';
import PropTypes from 'prop-types';
import SecuriteSociale from '../SecuriteSociale';
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
            label="Nom d'usage"
            stateRelatedMessage="Le champs est obligatoire"
            nativeInputProps={{name: 'nom', value: nom, onChange: ev => setNom(ev.target.value)}}
          />
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-3">
        <Input
          label="Nom de naissance"
          nativeInputProps={{name: 'nomNaissance', value: nomNaissance, onChange: ev => setNomNaissance(ev.target.value)}}
        />
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-3">
          <Input
            label="Adresse courriel"
            nativeInputProps={{value: personnePhysique.email}}
            disabled
          />
        </div>
      </div>
      <div className="fr-grid-row">
        <div className="fr-col-3">
          <Input
            label="Prénom(s)"
            stateRelatedMessage="Le champs est obligatoire"
            nativeInputProps={{placeholder: "Premier prénom", name: 'prenom1', value: prenom1, onChange: ev => setPrenom1(ev.target.value)}}
          />
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-3">
          <Input
            label="&nbsp;"
            nativeInputProps={{placeholder: "Deuxième prénom", name: 'prenom2', value: prenom2, onChange: ev => setPrenom2(ev.target.value)}}
          />
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-3">
          <Input
            label="&nbsp;"
            nativeInputProps={{placeholder: "Troisième prénom", name: 'prenom3', value: prenom3, onChange: ev => setPrenom3(ev.target.value)}}
          />
        </div>
        <div className="fr-col-1">
        </div>
      </div>
      <div className="fr-grid-row">
        <div className="fr-col-3">
          <Input
            label="Date de naissance"
            nativeInputProps={{
              type: 'date',value: dateNaissance, onChange: ev=>setDateNaissance(ev.target.value)
            }}
          />
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-3">
          <Input
            label="Ville de naissance"
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
