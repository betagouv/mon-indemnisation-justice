import {
  FormSelect,
  FormSelectProps,
} from "@/apps/requerant/composants/champs/form/FormSelect";
import { Pays } from "@/apps/requerant/models";
import { plainToInstance } from "class-transformer";
import React from "react";
import donnesPays from "./pays.json";

export type PaysSelectProps = Omit<
  FormSelectProps,
  "children" | "nativeSelectProps"
> & {
  pays?: Pays;
  onSelectionne: (pays: Pays) => void;
};

const listePays = donnesPays
  .map(({ code, nom }) => plainToInstance(Pays, { code, nom }))
  .sort((a: Pays, b: Pays) => {
    // On place la France en haut de la liste
    if (a.estFrance()) {
      return -1;
    }
    if (b.estFrance()) {
      return 1;
    }

    // Pour le reste, on trie par ordre alphabétique
    return a.nom.localeCompare(b.nom);
  });

export const PaysSelect = ({
  pays,
  onSelectionne,
  ...props
}: PaysSelectProps) => {
  return (
    <>
      <FormSelect
        {...props}
        nativeSelectProps={{
          defaultValue: pays?.code ?? "",
          onChange: (e) => {
            const pays = listePays.find(
              (pays: Pays) => pays.code === e.target.value,
            );

            if (pays) {
              onSelectionne(pays);
            }
          },
        }}
      >
        <option value="" disabled hidden>
          Selectionnez une option
        </option>
        {listePays.map((pays: Pays) => (
          <option key={pays.code} value={pays.code}>
            {pays.nom}
          </option>
        ))}
      </FormSelect>
    </>
  );
};
