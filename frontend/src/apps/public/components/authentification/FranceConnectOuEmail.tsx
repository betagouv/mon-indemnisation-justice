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
       <div className="fr-grid-row fr-grid-row--center">
        <div className="fr-notice fr-notice--info fr-mb-2w" style={{ width: "100%" }}>
          <div className="fr-container">
            <div className="fr-notice__body">
              <p>
                FranceConnect est la solution proposée par l’État pour sécuriser et simplifier la connexion à vos
                services en ligne
              </p>
            </div>
          </div>
        </div>

        <LienFranceConnect action={action} />
      </div>

      <div
        style={{ display: "flex", alignItems: "center", gap: "1rem", margin: "1.5rem 0", color: "var(--text-mention-grey)" }}
      >
        <hr style={{ flex: 1, border: 0, borderTop: "1px solid var(--border-default-grey)" }} />
        ou
        <hr style={{ flex: 1, border: 0, borderTop: "1px solid var(--border-default-grey)" }} />
      </div>

      <div className="fr-grid-row fr-grid-row--center">
        <Button priority="secondary" size="large" onClick={() => setAfficherEmail(true)} nativeButtonProps={{ type: "button" }}>
          {labelBoutonEmail}
        </Button>
      </div>
    </div>
  );
}
