import { Alert } from "@codegouvfr/react-dsfr/Alert";
import React from "react";

export function PageActivationCompteEnvoyee() {
  return (
    <Alert
      severity="success"
      title="Vérifiez votre boîte email"
      description="Nous venons de vous envoyer un lien pour activer votre compte. Ouvrez votre messagerie et cliquez sur ce lien pour finaliser votre inscription."
    />
  );
}
