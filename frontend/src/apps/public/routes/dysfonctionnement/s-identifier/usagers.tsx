import { TypePersonne } from "@/apps/public/components/authentification/etatAuthentification.ts";
import { FormulaireConnexion } from "@/apps/public/components/authentification/FormulaireConnexion.tsx";
import { FormulaireInscriptionUsager } from "@/apps/public/components/authentification/FormulaireInscriptionUsager.tsx";
import { FranceConnectOuEmail } from "@/apps/public/components/authentification/FranceConnectOuEmail.tsx";
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import { createFileRoute } from "@tanstack/react-router";
import React, { useEffect, useRef, useState } from "react";
import Breadcrumb from "@codegouvfr/react-dsfr/Breadcrumb";
import { Layout } from "@/apps/public/components/Layout.tsx";

export const Route = createFileRoute("/dysfonctionnement/s-identifier/usagers")(
  {
    component: RouteComponent,
  },
);

function RouteComponent() {
  const [estPersonneMorale, setPersonneMorale] = useState<boolean | undefined>(
    undefined,
  );
  const [estDejaInscrit, setDejaInscrit] = useState<boolean | undefined>(
    undefined,
  );

  const personneMoraleRef = useRef<HTMLDivElement>(null);
  const dejaInscritRef = useRef<HTMLDivElement>(null);

  const [refAScroller, setRefAScroller] =
    useState<React.RefObject<HTMLDivElement | null> | null>(null);

  useEffect(() => {
    refAScroller?.current?.scrollIntoView({
      behavior: "smooth",
      block: "start",
    });
  }, [refAScroller]);

  return (
    <Layout>
      <Breadcrumb
        currentPageLabel="Test d'éligibilité"
        homeLinkProps={{ href: "/" }}
        segments={[
          {
            label: "Déclarer un délai déraisonnable de procédure",
            linkProps: { to: "/dysfonctionnement/tester-mon-eligibilite/" },
          },
        ]}
      />
      <h1>Test d'éligibilité</h1>

      <div style={{ display: "flex", flexDirection: "column", gap: "2rem" }}>
        <div ref={personneMoraleRef}>
          <RadioButtons
            legend="La procédure concerne-t-elle une personne morale ?"
            options={[
              {
                label: "Oui",
                nativeInputProps: {
                  checked: estPersonneMorale == true,
                  onClick: () => {
                    setPersonneMorale(true);
                    setRefAScroller(dejaInscritRef);
                  },
                },
              },
              {
                label: "Non",
                nativeInputProps: {
                  checked: estPersonneMorale == false,
                  onClick: () => {
                    setPersonneMorale(false);
                    setRefAScroller(dejaInscritRef);
                  },
                },
              },
            ]}
          />
        </div>

        {estPersonneMorale != undefined && (
          <div ref={dejaInscritRef}>
            <RadioButtons
              legend="Êtes-vous déjà inscrit(e) sur la plateforme ?"
              options={[
                {
                  label: "Oui",
                  nativeInputProps: {
                    checked: estDejaInscrit == true,
                    onClick: () => setDejaInscrit(true),
                  },
                },
                {
                  label: "Non",
                  nativeInputProps: {
                    checked: estDejaInscrit == false,
                    onClick: () => setDejaInscrit(false),
                  },
                },
              ]}
            />
          </div>
        )}

        {estPersonneMorale == true && estDejaInscrit ? (
          <FormulaireConnexion labelEmail="Adresse email professionnelle" />
        ) : (
          <FormulaireInscriptionUsager
            typePersonne={TypePersonne.Morale}
            onSucces={() => console.log("Inscription PM")}
          />
        )}

        {estPersonneMorale == false && estDejaInscrit ? (
          <FranceConnectOuEmail
            action="S’identifier avec"
            labelBoutonEmail="Connexion par email"
          >
            <FormulaireConnexion labelEmail="Adresse email" />
          </FranceConnectOuEmail>
        ) : (
          <FranceConnectOuEmail
            action="S’inscrire avec"
            labelBoutonEmail="Inscription par email"
          >
            <FormulaireInscriptionUsager
              typePersonne={TypePersonne.Physique}
              onSucces={() => console.log("Inscription PP")}
            />
          </FranceConnectOuEmail>
        )}
      </div>
    </Layout>
  );
}
