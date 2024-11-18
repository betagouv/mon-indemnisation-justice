import React, {useState,useEffect,useRef} from 'react';
import { Input } from "@codegouvfr/react-dsfr/Input";

const Adresse = ({adresse ,optionalLigne1Texte=null}) => {

  const [ligne1,setLigne1]=useState(adresse.ligne1 ?? "");
  const [ligne2,setLigne2]=useState(adresse.ligne2 ?? "");
  const [ligne3,setLigne3]=useState(adresse.ligne3 ?? "");
  const [codePostal,setCodePostal]=useState(adresse.codePostal ?? "");
  const [localite,setLocalite]=useState(adresse?.localite ?? "");
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
    const url =Routing.generate('_api_adresse_patch',{id: adresse.id});
    const data = { ligne1: ligne1, ligne2: ligne2, ligne3: ligne3,
      codePostal:codePostal, localite: localite };
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
  },[ligne1,ligne2,ligne3,codePostal,localite]);

  const ligne1Text = (optionalLigne1Texte!==null) ? optionalLigne1Texte : "Adresse complète";

  return (
    <div className="fr-grid-row fr-grid-row--gutters">
      <div className="fr-col-12">
        <Input
          label={ligne1Text}
          nativeInputProps={{
            name: 'ligne1',
            value: ligne1,
            onChange: ev => setLigne1(ev.target.value),
            maxLength: 255
          }}
        />
      </div>
      <div className="fr-col-lg-2 fr-col-4">
        <Input
          label="Code postal"
          nativeInputProps={{
            name: 'codePostal',
            value: codePostal,
            onChange: ev => setCodePostal(ev.target.value),
            maxLength: 5
          }}
        />
      </div>
      <div className="fr-col-lg-10 fr-col-8">
        <Input
          label="Ville"
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
