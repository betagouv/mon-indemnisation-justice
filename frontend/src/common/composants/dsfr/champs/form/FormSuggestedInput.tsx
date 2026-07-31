import { BaseSuggestedInput } from "@common/composants/dsfr/champs/SuggestedInput";
import { CheckSuggestedInputProps } from "@common/composants/dsfr/champs/check/CheckSuggestedInput.tsx";
import { FormInput } from "@common/composants/dsfr/champs/form/FormInput";
import { TanstackFormField } from "@common/composants/dsfr/champs/form/TanstackFormField";
import React from "react";

export type FormSuggestedInputProps<TSuggestion extends {} = {}> = Omit<
  CheckSuggestedInputProps<TSuggestion>,
  "message" | "validation" | "estValide"
> & {
  champ: TanstackFormField;
};

export const FormSuggestedInput = <TSuggestion extends {} = {}>({
  onSelectionne,
  suggestions,
  filtre,
  rafraichisseur,
  rafraichisseurDebounceMs,
  estARafraichir,
  nativeInputProps,
  className,
  ...inputProps
}: FormSuggestedInputProps<TSuggestion>) => {
  return (
    <BaseSuggestedInput
      className={className}
      renderInput={({ onFocus, onBlur, onChange, addon }) => (
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
          addon={addon}
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
