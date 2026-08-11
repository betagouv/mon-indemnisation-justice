import { Button } from "@codegouvfr/react-dsfr/Button";
import React, { useState } from "react";
import { LienFranceConnect } from "./LienFranceConnect";

type FranceConnectOuEmailProps = {
  action: "S’identifier avec" | "S’inscrire avec";
  labelBoutonEmail: string;
  children: React.ReactNode;
};

/**
 * Pousse FranceConnect en priorité pour la personne physique (inscription ET connexion),
 * avec un bouton tertiaire discret pour basculer vers le formulaire email.
 */
export function FranceConnectOuEmail({ action, labelBoutonEmail, children }: FranceConnectOuEmailProps) {
  const [afficherEmail, setAfficherEmail] = useState(false);

  if (afficherEmail) {
    return <>{children}</>;
  }

  return (
    <div>
      <LienFranceConnect action={action} />
      <Button priority="tertiary no outline" onClick={() => setAfficherEmail(true)} nativeButtonProps={{ type: "button" }}>
        {labelBoutonEmail}
      </Button>
    </div>
  );
}
