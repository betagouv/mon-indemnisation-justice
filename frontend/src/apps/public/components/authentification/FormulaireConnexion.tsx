import { ButtonsGroup } from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Input } from "@codegouvfr/react-dsfr/Input";
import React from "react";
import { obtenirJetonCsrf } from "@/apps/public/services/reactArguments";

type FormulaireConnexionProps = {
  labelEmail: string;
};

const URL_CONNEXION = "/connexion-requerant";

const URL_MOT_DE_PASSE_OUBLIE = "/connexion";

export function FormulaireConnexion({ labelEmail }: FormulaireConnexionProps) {
  const jetonCsrf = obtenirJetonCsrf();

  return (
    <form method="POST" action={URL_CONNEXION}>
      <input type="hidden" name="_csrf_token" value={jetonCsrf} />

      <Input
        label={labelEmail}
        nativeInputProps={{ name: "_username", type: "email", autoComplete: "email", required: true }}
      />

      <Input
        label="Mot de passe"
        nativeInputProps={{ name: "_password", type: "password", autoComplete: "current-password", required: true }}
      />

      <p>
        <a href={URL_MOT_DE_PASSE_OUBLIE} target="_self" className="fr-link fr-text--sm">
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
