import React from "react";
import { Select } from "@codegouvfr/react-dsfr/Select";
import { randomId } from "@/apps/requerant/dossier/services/Random.ts";

const Civilite = ({
  civilite,
  setCivilite,
  defaultOptionText = null,
}: {
  civilite: string;
  setCivilite: (civilite: string) => void;
  defaultOptionText?: string;
}) => {
  const civilites = {
    M: "Monsieur",
    MME: "Madame",
  };

  return (
    <Select
      label="Civilité"
      nativeSelectProps={{
        id: randomId(),
        name: "civilite",
        onChange: (event) => setCivilite(event.target.value),
        value: civilite ?? "",
      }}
    >
      <option value="" disabled hidden>
        {defaultOptionText ?? "Sélectionnez une option"}
      </option>
      {Object.entries(civilites).map(([key, label]) => (
        <option key={key} value={key}>
          {label}
        </option>
      ))}
    </Select>
  );
};

export default Civilite;
