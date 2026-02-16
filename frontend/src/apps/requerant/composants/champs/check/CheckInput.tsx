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
  validation: boolean;
  estValide?: boolean;
};

export const CheckInput = ({
  estRequis,
  message,
  validation = true,
  estValide,
  label,
  ...props
}: CheckInputProps) => {
  return (
    <Input
      label={
        <>
          {label}
          {estRequis && (
            <span className="fr-text-default--error"> &#x2217;</span>
          )}
        </>
      }
      state={
        validation && !!message ? (estValide ? "success" : "error") : "default"
      }
      stateRelatedMessage={validation && !!message ? message : ""}
      /* Hack : on caste les `props` en `any` pour empêcher TS de se plaindre de type hint lié à l'aplatissement du type
       _discriminated union_ une fois déstructuré */
      {...(props as any)}
    />
  );
};
