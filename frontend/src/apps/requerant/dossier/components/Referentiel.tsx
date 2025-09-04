import React from "react";
import { Select } from "@codegouvfr/react-dsfr/Select";
const Referentiel = ({
  label,
  options,
  content = null,
  setContent,
}: {
  label: string;
  options: [string, any][];
  content?: string;
  setContent: (content: string) => void;
}) => {
  return (
    <>
      <Select
        label={label}
        nativeSelectProps={{
          onChange: (event) => setContent(event.target.value),
          value: content || "",
        }}
      >
        <option value="" disabled hidden>
          Selectionnez une option
        </option>
        {options.map(([key, label]) => (
          <option key={key} value={key}>
            {label}
          </option>
        ))}
      </Select>
    </>
  );
};

export default Referentiel;
