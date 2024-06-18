import React, {useState,useEffect} from 'react';
import { Select } from "@codegouvfr/react-dsfr/Select";
import { trans, USER_FIELD_CIVILITE,
GLOBAL_SELECT_OPTION } from '../../translator';

const Civilite = ({civilite,setCivilite, defaultOptionText=null}) => {

  const [civilites,setCivilites]=useState([]);
  const [isLoading,setIsLoading]=useState(false);
  useEffect(() => {
    if(true===isLoading)
      return;
    const route = Routing.generate("_api_civilite_get_collection");
    fetch(route)
      .then( (res) => res.json() )
      .then( (data) => setCivilites(data['hydra:member']) )
    ;
    setIsLoading(true);
  },[isLoading]);

  const defaultText = defaultOptionText??trans(GLOBAL_SELECT_OPTION);
  return (
    <Select
      label={trans(USER_FIELD_CIVILITE)}
      nativeSelectProps={{
          onChange: event => setCivilite(event.target.value),
          value: civilite??""
      }}
    >
      <option value="" disabled hidden>{defaultText}</option>
      {civilites.map((item) => <option key={item["@id"]} value={item["@id"]}>{item.libelle}</option>)}
    </Select>
  );
}

export default Civilite;