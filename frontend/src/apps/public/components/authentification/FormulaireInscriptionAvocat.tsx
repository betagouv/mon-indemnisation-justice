import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { ButtonsGroup } from "@codegouvfr/react-dsfr/ButtonsGroup";
import { FormInput } from "@common/composants/dsfr/champs/form/FormInput.tsx";
import { FormSuggestedInput } from "@common/composants/dsfr/champs/form/FormSuggestedInput.tsx";
import { useForm } from "@tanstack/react-form";
import React, { useEffect, useRef, useState } from "react";
import { ChampsBaseInscription } from "./ChampsBaseInscription";
import { listerBarreaux } from "./BarreauService";
import { rechercherAvocats } from "./AvocatService";
import { appliquerErreursChamps, ErreurInscription, inscrireAvocat } from "./AuthentificationService";
import { AvocatTrouve, Barreau, InscriptionAvocat, SchemaInscriptionAvocat } from "./authentification.schemas";

type FormulaireInscriptionAvocatProps = {
  onSucces: () => void;
};

// Les noms de barreaux du CNB sont en capitales ("AGEN") ; on les met en forme pour l'affichage.
function libelleBarreau(barreau: Barreau): string {
  const nomFormate = barreau.nom
    .toLowerCase()
    .replace(/(^|\s|-)\p{L}/gu, (lettre) => lettre.toUpperCase());

  return `Barreau de ${nomFormate}`;
}

const VALEURS_INITIALES: InscriptionAvocat = {
  civilite: undefined as unknown as InscriptionAvocat["civilite"],
  prenom: "",
  nom: "",
  nomNaissance: "",
  courriel: "",
  telephone: "",
  motDePasse: "",
  confirmation: "",
  cguOk: undefined as unknown as true,
  barreau: undefined as unknown as Barreau,
  numeroCnbf: "",
};

export function FormulaireInscriptionAvocat({ onSucces }: FormulaireInscriptionAvocatProps) {
  const [erreurGenerale, setErreurGenerale] = useState<string | null>(null);
  const barreauxRef = useRef<Barreau[]>([]);

  useEffect(() => {
    listerBarreaux()
      .then((donnees) => {
        barreauxRef.current = donnees;
      })
      .catch(() => {
        barreauxRef.current = [];
      });
  }, []);

  const formulaire = useForm({
    validators: { onSubmit: SchemaInscriptionAvocat },
    defaultValues: VALEURS_INITIALES,
    onSubmit: async ({ value, formApi }) => {
      if (!formApi.state.isValid) {
        return;
      }
      setErreurGenerale(null);
      try {
        await inscrireAvocat(value);
        onSucces();
      } catch (erreur) {
        if (erreur instanceof ErreurInscription && Object.keys(erreur.erreursChamps).length > 0) {
          // Le backend valide `barreauId` (c'est ce qui est envoyé sur le fil, cf. AuthentificationService.inscrireAvocat)
          // mais le champ du formulaire s'appelle `barreau` (il porte l'objet Barreau complet, pas seulement son id).
          appliquerErreursChamps(formulaire, erreur.erreursChamps, { barreauId: "barreau" });
        } else {
          setErreurGenerale("Une erreur est survenue, veuillez réessayer.");
        }
      }
    },
  });

  // Sélection d'un avocat depuis l'annuaire : on pré-remplit les champs déduits
  const identifierDepuisRecherche = (avocat: AvocatTrouve) => {
    formulaire.setFieldValue("nom", avocat.nom);
    formulaire.setFieldValue("prenom", avocat.prenom);
    formulaire.setFieldValue("barreau", avocat.barreau);
    formulaire.setFieldValue("numeroCnbf", avocat.numeroCnbf);
    if (avocat.civilite) formulaire.setFieldValue("civilite", avocat.civilite);
    if (avocat.telephone) formulaire.setFieldValue("telephone", avocat.telephone);
    if (avocat.email) formulaire.setFieldValue("courriel", avocat.email);
  };

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
      <ChampsBaseInscription
        formulaire={formulaire}
        morale={true}
        champsNomPrenom={
          <div className="fr-grid-row fr-grid-row--gutters">
            <formulaire.Field
              name="nom"
              children={(field) => (
                <FormSuggestedInput<AvocatTrouve>
                  label="Nom"
                  hintText="Tapez votre nom pour vous identifier grâce à l'annuaire national des avocats"
                  className="fr-col-6"
                  champ={field}
                  estRequis
                  nativeInputProps={{
                    // Sans ça, le navigateur peut proposer ses propres suggestions d'autocomplétion
                    autoComplete: "off",
                    value: field.state.value,
                    onChange: (e) => field.handleChange(e.target.value),
                  }}
                  estARafraichir={(valeur) => valeur.trim().length >= 2}
                  rafraichisseur={async (valeur) => {
                    const avocats = await rechercherAvocats(valeur);

                    return avocats.map((avocat) => ({
                      libelle: `${avocat.nom} ${avocat.prenom}`,
                      valeur: avocat,
                    }));
                  }}
                  rafraichisseurDebounceMs={500}
                  onSelectionne={(avocat) => {
                    identifierDepuisRecherche(avocat);

                    return avocat.nom;
                  }}
                />
              )}
            />
            <formulaire.Field
              name="prenom"
              children={(field) => (
                <FormInput
                  label="Prénom"
                  hintText=" "
                  className="fr-col-6"
                  champ={field}
                  estRequis
                  nativeInputProps={{ value: field.state.value, onChange: (e) => field.handleChange(e.target.value) }}
                />
              )}
            />
          </div>
        }
        champsApresNomNaissance={
          <div className="fr-grid-row fr-grid-row--gutters fr-mt-3w">
            <formulaire.Field
              name="barreau"
              children={(field) => (
                <FormSuggestedInput<Barreau>
                  key={field.state.value?.id ?? "vide"}
                  label="Barreau d'appartenance"
                  hintText=" "
                  className="fr-col-6"
                  champ={field}
                  estRequis
                  nativeInputProps={{
                    placeholder: "Paris, Lyon, ...",
                    autoComplete: "off",
                    defaultValue: field.state.value ? libelleBarreau(field.state.value) : "",
                  }}
                  estARafraichir={() => true}
                  rafraichisseurDebounceMs={0}
                  rafraichisseur={async (valeur) => {
                    const recherche = valeur.trim().toLowerCase();

                    return barreauxRef.current
                      .filter((barreau) => barreau.nom.toLowerCase().startsWith(recherche))
                      .map((barreau) => ({ libelle: libelleBarreau(barreau), valeur: barreau }));
                  }}
                  onSelectionne={(barreau) => {
                    field.setValue(barreau);
                    return libelleBarreau(barreau);
                  }}
                />
              )}
            />
            <formulaire.Field
              name="numeroCnbf"
              children={(field) => (
                <FormInput
                  label="Numéro CNBF"
                  hintText="6 chiffres"
                  className="fr-col-6"
                  champ={field}
                  estRequis
                  nativeInputProps={{
                    maxLength: 6,
                    inputMode: "numeric",
                    value: field.state.value,
                    onChange: (e) => field.handleChange(e.target.value),
                  }}
                />
              )}
            />
          </div>
        }
      />

      {erreurGenerale && <Alert className="fr-mb-3w" severity="error" title={erreurGenerale} />}

      <ButtonsGroup
        inlineLayoutWhen="always"
        alignment="right"
        buttons={[{ nativeButtonProps: { type: "submit" }, children: "S'inscrire" }]}
      />
    </form>
  );
}
