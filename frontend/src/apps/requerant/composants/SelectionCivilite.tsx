import {
  FormSelect,
  FormSelectProps,
} from "@/apps/requerant/composants/champs/form/FormSelect.tsx";
import {
  Civilite,
  Civilites,
  getCivilite,
  getCiviliteLibelle,
} from "@/apps/requerant/models";
import React from "react";

export const SelectionCivilite = ({
  civilite,
  label = "Civilité",
  onChange,
  nativeSelectProps,
  ...props
}: Omit<FormSelectProps, "children" | "label" | "nativeSelectProps"> & {
  civilite?: Civilite;
  label?: string;
  onChange: (civilite: Civilite) => void;
  nativeSelectProps?: React.DetailedHTMLProps<
    React.SelectHTMLAttributes<HTMLSelectElement>,
    HTMLSelectElement
  >;
}) => {
  return (
    <FormSelect
      label={label}
      {...props}
      nativeSelectProps={{
        ...(nativeSelectProps || {}),
        onChange: (e) => {
          const civilite = getCivilite(e.target.value);

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
    </FormSelect>
  );
};
