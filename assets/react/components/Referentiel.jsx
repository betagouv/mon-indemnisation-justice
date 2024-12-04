import React from 'react';
import { Select } from "@codegouvfr/react-dsfr/Select";
const Referentiel = ({label, options,content,setContent}) => {

  return (
    <>
      <Select
        label={label}
        nativeSelectProps={{
            onChange: event => setContent(event.target.value),
            value: content || ""
        }}
      >
        <option value="" disabled hidden>Selectionnez une option</option>
        {options.map(([key, label]) => <option key={key} value={key}>{label}</option>)}
      </Select>
    </>
  );
}

export default Referentiel;
