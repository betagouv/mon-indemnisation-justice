import Checkbox from "@codegouvfr/react-dsfr/Checkbox";
import { FormInput } from "@common/composants/dsfr/champs/form/FormInput.tsx";
import { FormRadioButtons } from "@common/composants/dsfr/champs/form/FormRadioButtons.tsx";
import React from "react";

// Champs communs à l'inscription PP/PM/avocat (cf. DTO Inscription.php côté backend).
// Factorisé pour éviter de dupliquer ce bloc entre FormulaireInscriptionUsager et FormulaireInscriptionAvocat,
// qui utilisent chacun leur propre instance de formulaire tanstack (schémas Zod différents) — d'où le typage `any`.
//
// `champsNomPrenom` permet à l'appelant de personnaliser le rendu des champs nom/prénom (ex : avocat, qui a besoin
// d'un champ Nom couplé à la recherche dans l'annuaire des avocats) sans dupliquer le reste du bloc.
// `champsAvantCgu` insère des champs supplémentaires (ex : barreau/CNBF pour l'avocat) juste avant la case CGU,
// pour que l'acceptation des conditions reste bien le dernier geste avant validation.
export function ChampsBaseInscription({
  formulaire,
  morale,
  champsNomPrenom,
  champsAvantCgu,
}: {
  formulaire: any;
  morale: boolean;
  champsNomPrenom?: React.ReactNode;
  champsAvantCgu?: React.ReactNode;
}) {
  return (
    <>
      <formulaire.Field
        name="civilite"
        children={(field: any) => (
          <FormRadioButtons
            legend="Civilité"
            champ={field}
            orientation="horizontal"
            options={[
              { label: "Monsieur", nativeInputProps: { checked: field.state.value === "M", onChange: () => field.handleChange("M") } },
              { label: "Madame", nativeInputProps: { checked: field.state.value === "MME", onChange: () => field.handleChange("MME") } },
            ]}
          />
        )}
      />

      {champsNomPrenom ?? (
        <div className="fr-grid-row fr-grid-row--gutters">
          <formulaire.Field
            name="prenom"
            children={(field: any) => (
              <FormInput
                label="Prénom"
                className="fr-col-6"
                champ={field}
                estRequis
                nativeInputProps={{ value: field.state.value, onChange: (e: any) => field.handleChange(e.target.value) }}
              />
            )}
          />
          <formulaire.Field
            name="nom"
            children={(field: any) => (
              <FormInput
                label="Nom"
                className="fr-col-6"
                champ={field}
                estRequis
                nativeInputProps={{ value: field.state.value, onChange: (e: any) => field.handleChange(e.target.value) }}
              />
            )}
          />
        </div>
      )}

      <formulaire.Field
        name="nomNaissance"
        children={(field: any) => (
          <FormInput
            label="Nom de naissance"
            hintText="Si différent du nom d'usage"
            champ={field}
            nativeInputProps={{ value: field.state.value, onChange: (e: any) => field.handleChange(e.target.value) }}
          />
        )}
      />

      <formulaire.Field
        name="courriel"
        children={(field: any) => (
          <FormInput
            label={morale ? "Adresse email professionnelle" : "Adresse email"}
            champ={field}
            estRequis
            nativeInputProps={{
              type: "email",
              autoComplete: "email",
              value: field.state.value,
              onChange: (e: any) => field.handleChange(e.target.value),
            }}
          />
        )}
      />

      <formulaire.Field
        name="telephone"
        children={(field: any) => (
          <FormInput
            label={morale ? "Téléphone professionnel" : "Téléphone"}
            champ={field}
            estRequis
            nativeInputProps={{
              type: "tel",
              autoComplete: "tel",
              value: field.state.value,
              onChange: (e: any) => field.handleChange(e.target.value),
            }}
          />
        )}
      />

      <div className="fr-grid-row fr-grid-row--gutters">
        <formulaire.Field
          name="motDePasse"
          children={(field: any) => (
            <FormInput
              label="Mot de passe"
              hintText="8 caractères minimum, avec au moins 1 chiffre"
              className="fr-col-6"
              champ={field}
              estRequis
              nativeInputProps={{
                type: "password",
                autoComplete: "new-password",
                value: field.state.value,
                onChange: (e: any) => field.handleChange(e.target.value),
              }}
            />
          )}
        />
        <formulaire.Field
          name="confirmation"
          children={(field: any) => (
            <FormInput
              label="Confirmation du mot de passe"
              hintText=" "
              className="fr-col-6"
              champ={field}
              estRequis
              nativeInputProps={{
                type: "password",
                autoComplete: "new-password",
                value: field.state.value,
                onChange: (e: any) => field.handleChange(e.target.value),
              }}
            />
          )}
        />
      </div>

      {champsAvantCgu}

      <formulaire.Field
        name="cguOk"
        children={(field: any) => {
          // Contrairement aux champs FormInput/FormRadioButtons du bloc, `Checkbox` n'a pas d'équivalent "Form"
          // qui dérive automatiquement `state`/`stateRelatedMessage` de `champ.state.meta` — sans ce câblage manuel,
          // la case restait muette (pas de bordure ni de message rouge) quand on soumettait sans l'avoir cochée,
          // contrairement à tous les autres champs du formulaire.
          const enErreur = !field.state.meta.isValid;

          return (
            <Checkbox
              // Compense la marge négative du fr-grid-row--gutters précédent (motDePasse/confirmation, ou
              // champsAvantCgu), qui sinon colle la case CGU directement contre le bloc du dessus.
              className="fr-mt-3w"
              state={enErreur ? "error" : "default"}
              stateRelatedMessage={enErreur ? field.state.meta.errors.at(0)?.message : undefined}
              options={[
                {
                  label: "J'accepte les conditions générales d'utilisation",
                  nativeInputProps: {
                    checked: field.state.value === true,
                    onChange: (e: any) => field.handleChange(e.target.checked),
                  },
                },
              ]}
            />
          );
        }}
      />
    </>
  );
}
