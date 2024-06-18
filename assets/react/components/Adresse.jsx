import React, {useState,useEffect,useRef} from 'react';
import { Select } from "@codegouvfr/react-dsfr/Select";
import { Input } from "@codegouvfr/react-dsfr/Input";
import { trans, ADRESSE_FIELD_LIGNE1,
  ADRESSE_FIELD_CODE_POSTAL,
  ADRESSE_FIELD_LOCALITE,
  ADRESSE_FIELD_LIGNE1_DESCRIPTION
} from '../../translator';

const Adresse = ({adresse,optionalLigne1Texte=null}) => {

  const [ligne1,setLigne1]=useState(adresse.ligne1??"");
  const [ligne2,setLigne2]=useState(adresse.ligne2??"");
  const [ligne3,setLigne3]=useState(adresse.ligne3??"");
  const [codePostal,setCodePostal]=useState(adresse.codePostal??"");
  const [localite,setLocalite]=useState(adresse.localite??"");
  const [recordActived, setRecordActived]=useState(false);

  var keyUpTimer = useRef(null);
  const KEY_UP_TIMER_DELAY = 1000;

  function mustBeRecorded() {
    const test =
      (ligne1 !== adresse.ligne1) ||
      (ligne2 !== adresse.ligne2) ||
      (ligne3 !== adresse.ligne3) ||
      (codePostal !== adresse.codePostal) ||
      (localite !== adresse.localite) ||
      (true === recordActived)
    ;
    setRecordActived(test);
    return test;
  }

  useEffect(() => {
    if(false === mustBeRecorded())
      return;
    const url =Routing.generate('_api_adresse_patch',{id:adresse.id});
    const data = { ligne1: ligne1, ligne2: ligne2, ligne3: ligne3,
      codePostal:codePostal, localite: localite };
    clearTimeout(keyUpTimer.current);
    keyUpTimer.current = setTimeout(() => {
      fetch(url, {
        method: 'PATCH',
        headers: {'Content-Type': 'application/merge-patch+json'},
        body: JSON.stringify(data)
      })
      .then((response) => response.json())
      .then((data) => {})
      .catch(() => {})
      ;
    },KEY_UP_TIMER_DELAY);
  },[ligne1,ligne2,ligne3,codePostal,localite]);

  const ligne1Text = (optionalLigne1Texte!==null) ? optionalLigne1Texte : trans(ADRESSE_FIELD_LIGNE1);
  const ligne1HintText = (optionalLigne1Texte!==null) ? "" : trans(ADRESSE_FIELD_LIGNE1_DESCRIPTION);

  return (
    <div className="fr-grid-row fr-grid-row--gutters">
      <div className="fr-col-12">
        <Input
          label={ligne1Text}
          hintText={ligne1HintText}
          nativeInputProps={{
            name: 'ligne1',
            value: ligne1,
            onChange: ev => setLigne1(ev.target.value),
            maxLength: 255
          }}
        />
      </div>
      <div className="fr-col-2">
        <Input
          label={trans(ADRESSE_FIELD_CODE_POSTAL)}
          nativeInputProps={{
            name: 'codePostal',
            value: codePostal,
            onChange: ev => setCodePostal(ev.target.value),
            maxLength: 5
          }}
        />
      </div>
      <div className="fr-col-10">
        <Input
          label={trans(ADRESSE_FIELD_LOCALITE)}
          nativeInputProps={{
            name: 'localite',
            value: localite,
            onChange: ev => setLocalite(ev.target.value),
            maxLength: 255
          }}
        />
      </div>
    </div>
  );
}

export default Adresse;