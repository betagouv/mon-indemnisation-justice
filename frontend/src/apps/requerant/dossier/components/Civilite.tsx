import React from "react";
import { Select } from "@codegouvfr/react-dsfr/Select";
import { randomId } from "@/apps/requerant/dossier/services/Random.ts";

export const ChampCivilite = ({
  civilite,
  setCivilite,
  estActif = true,
}: {
  civilite?: string;
  setCivilite: (civilite: string) => void;
  estActif?: boolean;
}) => {
  const civilites = {
    M: "Monsieur",
    MME: "Madame",
  };

  return (
    <Select
      label="Civilité"
      disabled={!estActif}
      nativeSelectProps={{
        id: randomId(),
        name: "civilite",
        onChange: (event) => setCivilite(event.target.value),
        value: civilite ?? "",
      }}
    >
      <option value="" disabled hidden>
        Sélectionnez une option
      </option>
      {Object.entries(civilites).map(([key, label]) => (
        <option key={key} value={key}>
          {label}
        </option>
      ))}
    </Select>
  );
};
