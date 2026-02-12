import React from "react";
import {
  Civilite,
  Civilites,
  getCivilite,
  getCiviliteLibelle,
} from "@/apps/requerant/models";
import { Select, SelectProps } from "@codegouvfr/react-dsfr/Select";

export const SelectionCivilite = ({
  civilite,
  label = "Civilité",
  onChange,
  nativeSelectProps,
  ...props
}: Omit<SelectProps, "children" | "label" | "nativeSelectProps"> & {
  civilite?: Civilite;
  label?: string;
  onChange: (civilite: Civilite) => void;
  nativeSelectProps?: React.DetailedHTMLProps<
    React.SelectHTMLAttributes<HTMLSelectElement>,
    HTMLSelectElement
  >;
}) => {
  return (
    <Select
      label={label}
      {...props}
      nativeSelectProps={{
        ...(nativeSelectProps || {}),
        onChange: (e) => {
          const civilite = getCivilite(e.target.value);
          console.log(civilite);

          if (civilite) {
            onChange(civilite);
          }
        },
        value: civilite ?? "",
      }}
    >
      <option value="" disabled hidden>
        Sélectionnez une civilité
      </option>

      {Civilites.map((civilite: Civilite) => (
        <option key={civilite} value={civilite}>
          {getCiviliteLibelle(civilite)}
        </option>
      ))}
    </Select>
  );
};
