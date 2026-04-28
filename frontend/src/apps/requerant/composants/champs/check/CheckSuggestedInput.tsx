import BaseSuggestedInput, { BaseSuggestedInputProps } from "@/apps/requerant/composants/SuggestedInput.tsx";
import React from "react";
import { CheckInput, CheckInputProps } from "./CheckInput.tsx";

export type CheckSuggestedInputProps<TSuggestion extends {} = {}> = Omit<
  BaseSuggestedInputProps<TSuggestion>,
  "renderInput"
> &
  Omit<CheckInputProps, "textArea" | "nativeTextAreaProps">;

export const CheckSuggestedInput = <TSuggestion extends {} = {}>({
  onSelectionne,
  suggestions,
  filtre,
  rafraichisseur,
  rafraichisseurDebounceMs,
  estARafraichir,
  nativeInputProps,
  ...inputProps
}: CheckSuggestedInputProps<TSuggestion>) => {
  return (
    <BaseSuggestedInput
      renderInput={({ onFocus, onBlur, onChange }) => (
        <CheckInput
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
      {
        // On _caste_ volontairement les propriétés du composant de base puisque Typescript ne sait pas gérer les object
        // union types
        ...({
          onSelectionne,
          suggestions,
          rafraichisseur,
          rafraichisseurDebounceMs,
          estARafraichir,
        } as any)
      }
    />
  );
};
