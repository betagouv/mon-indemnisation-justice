import React, {useState,useEffect} from 'react';
import { Select } from "@codegouvfr/react-dsfr/Select";
import { Input } from "@codegouvfr/react-dsfr/Input";
import { trans, ADRESSE_FIELD_LIGNE1,
  ADRESSE_FIELD_CODE_POSTAL,
  ADRESSE_FIELD_LOCALITE
} from '../../translator';

const Adresse = ({adresse}) => {

  const [ligne1,setLigne1]=useState(adresse.ligne1??"");
  const [ligne2,setLigne2]=useState(adresse.ligne2??"");
  const [ligne3,setLigne3]=useState(adresse.ligne3??"");
  const [codePostal,setCodePostal]=useState(adresse.codePostal??"");
  const [localite,setLocalite]=useState(adresse.localite??"");

  useEffect(() => {
    const url =Routing.generate('_api_adresse_patch',{id:adresse.id});
    const data = { ligne1: ligne1, ligne2: ligne2, ligne3: ligne3,
      codePostal:codePostal, localite: localite };
    fetch(url, {
      method: 'PATCH',
      headers: {'Content-Type': 'application/merge-patch+json'},
      body: JSON.stringify(data)
    })
    .then((response) => response.json())
    .then((data) => console.log('backup adresse'))
    ;
  },[ligne1,ligne2,ligne3,codePostal,localite]);

  return (
    <div className="fr-grid-row">
      <div className="fr-col-12">
        <Input
          label={trans(ADRESSE_FIELD_LIGNE1)}
          nativeInputProps={{name: 'ligne1', value: ligne1, onChange: ev => setLigne1(ev.target.value)}}
        />
        <Input
          nativeInputProps={{name: 'ligne2', value: ligne2, onChange: ev => setLigne2(ev.target.value)}}
        />
        <Input
          nativeInputProps={{name: 'ligne3', value: ligne3, onChange: ev => setLigne3(ev.target.value)}}
        />
      </div>
      <div className="fr-col-3">
        <Input
          label={trans(ADRESSE_FIELD_CODE_POSTAL)}
          nativeInputProps={{name: 'codePostal', value: codePostal, onChange: ev => setCodePostal(ev.target.value)}}
        />
      </div>
      <div className="fr-col-1">
      </div>
      <div className="fr-col-8">
        <Input
          label={trans(ADRESSE_FIELD_LOCALITE)}
          nativeInputProps={{name: 'localite', value: localite, onChange: ev => setLocalite(ev.target.value)}}
        />
      </div>
    </div>
  );
}

export default Adresse;
