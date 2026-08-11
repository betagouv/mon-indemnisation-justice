import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { ButtonsGroup } from "@codegouvfr/react-dsfr/ButtonsGroup";
import { useForm } from "@tanstack/react-form";
import React, { useState } from "react";
import { ChampsBaseInscription } from "./ChampsBaseInscription";
import { appliquerErreursChamps, ErreurInscription, inscrireUsager } from "./AuthentificationService";
import { InscriptionUsager, SchemaInscriptionUsager } from "./authentification.schemas";
import { TypePersonne } from "./etatAuthentification";

type FormulaireInscriptionUsagerProps = {
  typePersonne: TypePersonne;
  onSucces: () => void;
};

const VALEURS_INITIALES: InscriptionUsager = {
  civilite: undefined as unknown as InscriptionUsager["civilite"],
  prenom: "",
  nom: "",
  nomNaissance: "",
  courriel: "",
  telephone: "",
  motDePasse: "",
  confirmation: "",
  cguOk: undefined as unknown as true,
};

export function FormulaireInscriptionUsager({ typePersonne, onSucces }: FormulaireInscriptionUsagerProps) {
  const morale = typePersonne === TypePersonne.Morale;
  const [erreurGenerale, setErreurGenerale] = useState<string | null>(null);

  const formulaire = useForm({
    validators: { onSubmit: SchemaInscriptionUsager },
    defaultValues: VALEURS_INITIALES,
    onSubmit: async ({ value, formApi }) => {
      if (!formApi.state.isValid) {
        return;
      }
      setErreurGenerale(null);
      try {
        await inscrireUsager(value, typePersonne);
        onSucces();
      } catch (erreur) {
        if (erreur instanceof ErreurInscription && Object.keys(erreur.erreursChamps).length > 0) {
          appliquerErreursChamps(formulaire, erreur.erreursChamps);
        } else {
          setErreurGenerale("Une erreur est survenue, veuillez réessayer.");
        }
      }
    },
  });

  return (
    <form
      onSubmit={async (e) => {
        e.preventDefault();
        e.stopPropagation();
        try {
          formulaire.validate("submit");
          await formulaire.handleSubmit();
        } catch (erreur) {
          console.error(erreur);
        }
      }}
    >
      <ChampsBaseInscription formulaire={formulaire} morale={morale} />

      {erreurGenerale && <Alert className="fr-mb-3w" severity="error" title={erreurGenerale} />}

      <ButtonsGroup
        inlineLayoutWhen="always"
        alignment="right"
        buttons={[{ nativeButtonProps: { type: "submit" }, children: "S'inscrire" }]}
      />
    </form>
  );
}
