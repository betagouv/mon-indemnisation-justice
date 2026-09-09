import { Layout } from "@/apps/public/components/Layout.tsx";
import Breadcrumb from "@codegouvfr/react-dsfr/Breadcrumb";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import { ButtonsGroup } from "@codegouvfr/react-dsfr/ButtonsGroup";
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import { createFileRoute } from "@tanstack/react-router";
import React, { useState } from "react";

const DysfonctionnementAccueilPage = () => {
  const [estAvocat, setAvocat] = useState<boolean | undefined>(undefined);

  return (
    <Layout>
      <Breadcrumb
        currentPageLabel="Déclarer un délai déraisonnable de procédure"
        homeLinkProps={{
          href: "/",
          target: "_self",
        }}
        segments={[]}
      />

      <h1>Déclarer un délai déraisonnable de procédure</h1>

      <div className="fr-grid-row fr-grid-row--gutters">
        <RadioButtons
          className="fr-col-12"
          orientation="horizontal"
          legend="Êtes-vous ?"
          options={[
            {
              label: "Un avocat",
              hintText: "Vous êtes mandaté par un ou plusieurs clients",
              nativeInputProps: {
                checked: true == estAvocat,
                onClick: () => setAvocat(true),
              },
            },
            {
              label: "Un usager",
              hintText:
                "Vous venez faire vos droits ou celle de l'entreprise ou l'association que vous représentez",
              nativeInputProps: {
                checked: false == estAvocat,
                onClick: () => setAvocat(false),
              },
            },
          ]}
        />

        <ButtonsGroup
          className="fr-col-12"
          alignment="right"
          inlineLayoutWhen="always"
          buttons={[
            {
              children: "Revenir à l'accueil",
              priority: "tertiary no outline",
              linkProps: {
                href: "/",
                target: "_self",
              },
            },
            ...((estAvocat
              ? [
                  {
                    children: "S'identifier",
                    linkProps: {
                      to: "/dysfonctionnement/s-identifier/avocats",
                    },
                  },
                ]
              : [
                  {
                    children: "Tester mon éligibilité",
                    linkProps: {
                      to: "/dysfonctionnement/tester-mon-eligibilite",
                      disabled: undefined === estAvocat,
                    },
                  },
                ]) as ButtonProps[]),
          ]}
        />
      </div>
    </Layout>
  );
};

export const Route = createFileRoute("/dysfonctionnement/")({
  component: DysfonctionnementAccueilPage,
});
