import {
  RegExpInput,
  RegExpInputProps,
} from "@common/composants/dsfr/champs/RegExpInput.tsx";
import React from "react";

export type CodePostalInputProps = Omit<
  RegExpInputProps,
  "motif" | "empecherLaSelection" | "nettoyage"
>;

export const CodePostalInput = ({
  nativeInputProps,
  ...props
}: CodePostalInputProps) => {
  return (
    <>
      <RegExpInput
        {...props}
        motif="^[0-9]{0,5}$"
        empecherLaSelection={true}
        nettoyage={{}}
        nativeInputProps={{
          ...nativeInputProps,
          type: "text",
          maxLength: 5,
        }}
      />
    </>
  );
};
