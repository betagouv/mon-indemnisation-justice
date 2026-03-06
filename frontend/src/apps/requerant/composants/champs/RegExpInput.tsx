import {
  FormInput,
  FormInputProps,
} from "@/apps/requerant/composants/champs/form/FormInput.tsx";
import React, { KeyboardEvent } from "react";

export type RegExpInputProps = FormInputProps & {
  motif: string;
  empecherLaSelection?: boolean;
  // Fonctions de nettoyage
  nettoyage: {
    // Nettoyage de la valeur du champ après saisie
    onSaisie?: (valeur: string) => string;
    // Nettoyage de la valeur avant analyse
    onModifie?: (valeur: string) => string;
  };
};
export const RegExpInput = ({
  motif,
  empecherLaSelection = false,
  nativeInputProps,
  ...props
}: RegExpInputProps) => {
  return (
    <>
      <FormInput
        {...props}
        nativeInputProps={{
          ...nativeInputProps,
          pattern: motif,
          onFocus: empecherLaSelection
            ? (e) => {
                // Mettre le curseur à la fin
                e.target.setSelectionRange(
                  e.target.value.length + 1,
                  e.target.value.length + 1,
                );
              }
            : undefined,
          onKeyDown: (e: KeyboardEvent<HTMLInputElement>) => {
            if (e.key !== "Backspace" && e.key !== "Tab") {
              const target: HTMLInputElement = e.target as HTMLInputElement;

              // Projection de la valeur du champ après la modification
              const valeurProjetee =
                target.value.substring(0, target.selectionStart || 0) +
                e.key +
                target.value.substring(target.selectionEnd || 0);

              if (!valeurProjetee.match(new RegExp(target.pattern))) {
                e.stopPropagation();
                e.preventDefault();
              }
            }
          },
        }}
      />
    </>
  );
};
