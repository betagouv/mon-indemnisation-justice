import React, {
  DetailedHTMLProps,
  InputHTMLAttributes,
  type ReactNode,
} from "react";
import { Input } from "@codegouvfr/react-dsfr/Input";
import { type InputProps } from "@codegouvfr/react-dsfr/src/Input.tsx";

export type CheckInputProps = Omit<
  InputProps,
  "state" | "stateRelatedMessage"
> & {
  estRequis?: boolean;
  message?: ReactNode;
  estValide?: boolean;
};

export const CheckInput = ({
  estRequis,
  message,
  estValide,
  ...props
}: CheckInputProps) => {
  // Hack : on caste les `props` en `any` pour empêcher TS de se plaindre de type hint lié au type _discriminated union_
  const { label, ...baseProps } = props as any;

  return (
    <Input
      label={
        <>
          {label}
          {estRequis && (
            <span className="fr-text-default--error">&#x2217;</span>
          )}
        </>
      }
      state={
        estValide !== undefined ? (estValide ? "success" : "error") : "default"
      }
      stateRelatedMessage={message}
      {...baseProps}
    />
  );
};
