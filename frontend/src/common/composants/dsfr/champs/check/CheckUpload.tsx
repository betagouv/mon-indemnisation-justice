import { Upload, UploadProps } from "@codegouvfr/react-dsfr/Upload";
import React, { type ReactNode } from "react";
import { Requis } from "@/common/composants/dsfr/Requis.tsx";

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
          {estRequis && <Requis />}
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
