import { ButtonsGroup } from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Input } from "@codegouvfr/react-dsfr/Input";
import React from "react";
import { obtenirJetonCsrf } from "@/apps/public/services/reactArguments";

type FormulaireConnexionProps = {
  labelEmail: string;
};

export function FormulaireConnexion({ labelEmail }: FormulaireConnexionProps) {
  const jetonCsrf = obtenirJetonCsrf();

  return (
    <form method="POST" action="/connexion-requerant">
      <input type="hidden" name="_csrf_token" value={jetonCsrf} />

      <div className="fr-grid-row fr-grid-row--gutters">
        <Input
          label={labelEmail}
          className="fr-col-6"
          nativeInputProps={{ name: "_username", type: "email", autoComplete: "email", required: true }}
        />

        <Input
          label="Mot de passe"
          className="fr-col-6"
          nativeInputProps={{ name: "_password", type: "password", autoComplete: "current-password", required: true }}
        />
      </div>

      <p>
        <a href="/connexion" target="_self" className="fr-link fr-text--sm">
          Mot de passe oublié ?
        </a>
      </p>

      <ButtonsGroup
        inlineLayoutWhen="always"
        alignment="right"
        buttons={[{ nativeButtonProps: { type: "submit", disabled: !jetonCsrf }, children: "Se connecter" }]}
      />
    </form>
  );
}
