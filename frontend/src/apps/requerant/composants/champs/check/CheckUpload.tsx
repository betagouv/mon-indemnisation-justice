import { Upload, UploadProps } from "@codegouvfr/react-dsfr/Upload";
import React, { type ReactNode } from "react";

export type CheckUploadProps = Omit<
  UploadProps,
  "state" | "stateRelatedMessage"
> & {
  estRequis?: boolean;
  message?: ReactNode;
  validation: boolean;
  estValide?: boolean;
};

export const CheckUpload = ({
  estRequis,
  message,
  validation = true,
  estValide,
  label,
  ...props
}: CheckUploadProps) => {
  return (
    <Upload
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
      {...props}
    />
  );
};
