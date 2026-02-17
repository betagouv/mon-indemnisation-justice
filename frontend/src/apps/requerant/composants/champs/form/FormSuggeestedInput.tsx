import { CheckSuggestedInputProps } from "@/apps/requerant/composants/champs/check/CheckSuggestedInput.tsx";
import { FormInput } from "@/apps/requerant/composants/champs/form/FormInput.tsx";
import { TanstackFormField } from "@/apps/requerant/composants/champs/form/TanstackFormField";
import { BaseSuggestedInput } from "@/apps/requerant/composants/SuggestedInput.tsx";
import React from "react";

export type FormSuggestedInputProps<TSuggestion extends {} = {}> = Omit<
  CheckSuggestedInputProps<TSuggestion>,
  "message" | "validation" | "estValide"
> & {
  champ?: TanstackFormField;
};

export const FormSuggestedInput = <TSuggestion extends {} = {}>({
  onSelectionne,
  suggestions,
  filtre,
  rafraichisseur,
  rafraichisseurDebounceMs,
  estARafraichir,
  nativeInputProps,
  ...inputProps
}: FormSuggestedInputProps<TSuggestion>) => {
  return (
    <BaseSuggestedInput
      renderInput={({ onFocus, onBlur, onChange }) => (
        <FormInput
          nativeInputProps={{
            ...nativeInputProps,
            onFocus: (e) => {
              onFocus(e);
              nativeInputProps?.onFocus?.(e);
            },
            onBlur: (e) => {
              onBlur(e);
              nativeInputProps?.onBlur?.(e);
            },
            onChange: (e) => {
              onChange(e);
              nativeInputProps?.onChange?.(e);
            },
          }}
          {...inputProps}
        />
      )}
      // On _caste_ volontairement les propriétés du composant de base puisque Typescript ne sait pas gérer les object
      // union types
      {...({
        onSelectionne,
        suggestions,
        rafraichisseur,
        rafraichisseurDebounceMs,
        estARafraichir,
      } as any)}
    />
  );
};
