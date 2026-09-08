import { Barreau } from "@/apps/public/components/authentification/authentification.schemas.ts";
import { Layout } from "@/apps/public/components/Layout.tsx";
import Breadcrumb from "@codegouvfr/react-dsfr/Breadcrumb";
import { ButtonsGroup } from "@codegouvfr/react-dsfr/ButtonsGroup";
import Checkbox from "@codegouvfr/react-dsfr/Checkbox";
import { Input } from "@codegouvfr/react-dsfr/Input";
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import { CheckInput } from "@common/composants/dsfr/champs/check/CheckInput";
import { CheckRadioButtons } from "@common/composants/dsfr/champs/check/CheckRadioButtons.tsx";
import { CheckSuggestedInput } from "@common/composants/dsfr/champs/check/CheckSuggestedInput.tsx";
import { createFileRoute } from "@tanstack/react-router";
import React, { useMemo, useState } from "react";

const InscriptionAvocatFormulaire = () => {
  const barreauxRef = useMemo<Barreau[]>(() => [], []);

  return (
    <form method="POST" action="/dysfonctionnement/avocats/inscription">
      <h3>Inscription</h3>

      <div className="fr-grid-row fr-grid-row--gutters fr-my-0">
        <CheckRadioButtons
          className="fr-col-12"
          estRequis={true}
          validation={false}
          legend="Civilité"
          orientation="horizontal"
          options={[
            {
              label: "Monsieur",
              nativeInputProps: {},
            },
            {
              label: "Madame",
              nativeInputProps: {},
            },
          ]}
        />
      </div>

      <div className="fr-grid-row fr-grid-row--gutters fr-my-0">
        <CheckInput
          label="Nom"
          hintText="&zwnj;"
          className="fr-col-12 fr-col-lg-4"
          estRequis
          validation={false}
        />

        <CheckInput
          label="Prénom"
          hintText="&zwnj;"
          className="fr-col-12 fr-col-lg-4"
          estRequis
          validation={false}
        />

        <CheckInput
          label="Nom de naissance"
          hintText="Si différent du nom d'usage"
          className="fr-col-12 fr-col-lg-4"
          estRequis={false}
          validation={false}
        />
      </div>

      <div className="fr-grid-row fr-grid-row--gutters fr-my-0">
        <CheckSuggestedInput<Barreau>
          label="Barreau d'appartenance"
          hintText=" "
          className="fr-col-lg-3 fr-col-6"
          estRequis
          validation={false}
          nativeInputProps={{
            placeholder: "Paris, Lyon, ...",
            autoComplete: "off",
          }}
          estARafraichir={() => true}
          rafraichisseurDebounceMs={0}
          rafraichisseur={async (valeur) => {
            const recherche = valeur.trim().toLowerCase();

            return barreauxRef
              .filter((barreau) =>
                barreau.nom.toLowerCase().startsWith(recherche),
              )
              .map((barreau) => ({
                libelle: barreau.nom
                  .toLowerCase()
                  .replace(/(^|\s|-)\p{L}/gu, (lettre) => lettre.toUpperCase()),
                valeur: barreau,
              }));
          }}
          onSelectionne={(barreau) => {
            console.log(barreau);
          }}
        />

        <CheckInput
          label="Numéro CNBF"
          hintText="6 chiffres"
          className="fr-col-6  fr-col-lg-3"
          validation={false}
          estRequis
          nativeInputProps={{
            maxLength: 6,
            inputMode: "numeric",
          }}
        />
        <CheckInput
          label="Adresse email professionnelle"
          hintText="&zwnj;"
          className="fr-col-6 fr-col-lg-3"
          validation={false}
          estRequis
          nativeInputProps={{
            type: "email",
            autoComplete: "email",
          }}
        />
        <CheckInput
          label={"Téléphone professionnel"}
          hintText="&zwnj;"
          className="fr-col-6 fr-col-lg-3"
          validation={false}
          estRequis
          nativeInputProps={{
            type: "tel",
            autoComplete: "tel",
          }}
        />
      </div>

      <div className="fr-grid-row fr-grid-row--gutters">
        <CheckInput
          label="Mot de passe"
          hintText="8 caractères minimum, avec au moins 1 chiffre"
          className="fr-col-6"
          validation={false}
          estRequis
          nativeInputProps={{
            type: "password",
            autoComplete: "new-password",
          }}
        />
        <CheckInput
          label="Confirmation du mot de passe"
          hintText=" "
          className="fr-col-6"
          validation={false}
          estRequis
          nativeInputProps={{
            type: "password",
            autoComplete: "new-password",
          }}
        />
      </div>

      <div className="fr-grid-row fr-grid-row--gutters">
        <Checkbox
          options={[
            {
              label: "J'accepte les conditions générales d'utilisation",
              nativeInputProps: {},
            },
          ]}
        />
      </div>

      <div className="fr-grid-row fr-grid-row--gutters">
        <ButtonsGroup
          className="fr-col-12"
          inlineLayoutWhen="always"
          alignment="right"
          buttons={[
            {
              nativeButtonProps: { type: "submit" },
              children: "S'inscrire",
            },
          ]}
        />
      </div>
    </form>
  );
};

const ConnexionAvocatFormulaire = () => {
  return (
    <div className="fr-container--fluid">
      <h3>Connexion</h3>

      <form method="POST" action="/dysfonctionnement/avocats/connexion">
        {/*<input type="hidden" name="_csrf_token" value="" /> */}

        <div className="fr-grid-row fr-grid-row--gutters">
          <Input
            label="Adresse email"
            className="fr-col-lg-6 fr-col-12"
            nativeInputProps={{
              name: "_username",
              type: "email",
              autoComplete: "email",
              required: true,
            }}
          />

          <Input
            label="Mot de passe"
            className="fr-col-lg-6 fr-col-12"
            nativeInputProps={{
              name: "_password",
              type: "password",
              autoComplete: "current-password",
              required: true,
            }}
          />
        </div>

        <div className="fr-grid-row">
          <ButtonsGroup
            className="fr-col-12"
            inlineLayoutWhen="always"
            alignment="right"
            buttons={[
              {
                nativeButtonProps: { type: "button" },
                priority: "tertiary no outline",
                children: "Mot de passe oublié",
              },
              {
                nativeButtonProps: { type: "submit" },
                children: "Se connecter",
              },
            ]}
          />
        </div>
      </form>
    </div>
  );
};

const IdentificationAvocatPage = () => {
  const [estDejaInscrit, setDejaInscrit] = useState<boolean | undefined>(
    undefined,
  );

  return (
    <Layout>
      <Breadcrumb
        currentPageLabel="S'identifier en tant qu'avocat"
        homeLinkProps={{
          href: "/",
          activeOptions: { exact: true, explicitUndefined: false },
        }}
        segments={[
          {
            label: "Déclarer un déni de justice",
            linkProps: {
              to: "/dysfonctionnement",
              activeOptions: { exact: true },
            },
          },
        ]}
      />

      <h1>S'identifier en tant qu'avocat</h1>

      <div className="fr-grid-row fr-grid-row--gutters">
        <RadioButtons
          className="fr-col-12"
          orientation="horizontal"
          legend="Êtes-vous déjà enregistré sur la plateforme ?"
          options={[
            {
              label: "Oui",
              nativeInputProps: {
                checked: true == estDejaInscrit,
                onClick: () => setDejaInscrit(true),
              },
            },
            {
              label: "Non",
              nativeInputProps: {
                checked: false == estDejaInscrit,
                onClick: () => setDejaInscrit(false),
              },
            },
          ]}
        />
      </div>

      {estDejaInscrit === false && <InscriptionAvocatFormulaire />}

      {estDejaInscrit === true && <ConnexionAvocatFormulaire />}
    </Layout>
  );
};

export const Route = createFileRoute("/dysfonctionnement/s-identifier/avocats")(
  {
    component: IdentificationAvocatPage,
  },
);
