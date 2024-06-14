import React, {useState,useEffect} from 'react';
import { Select } from "@codegouvfr/react-dsfr/Select";
import { trans, USER_FIELD_CIVILITE } from '../../translator';
import { castUrl } from '../utils/cast';
const Referentiel = ({label,route,content,setContent}) => {

  const [referentiel,setReferentiel]=useState([]);
  const [isLoading,setIsLoading]=useState(false);
  const [value,setValue]=useState(castUrl(content));
  useEffect(() => {
    if(true===isLoading)
      return;
    fetch(route)
      .then( (res) => res.json() )
      .then( (data) => setReferentiel(data['hydra:member']) )
    ;
    setIsLoading(true);
  },[isLoading]);

  useEffect(() => {
    setContent(value);
  },[value]);

  return (
    <>
      {isLoading &&
      <Select
        label={label}
        nativeSelectProps={{
            onChange: event => setValue(event.target.value),
            value: value
        }}
      >
        <option value="" disabled hidden>Selectionnez une option</option>
        {referentiel.map((item) => <option key={item["@id"]} value={item["@id"]}>{item.libelle}</option>)}
      </Select>
    }
    </>
  );
}

export default Referentiel;
