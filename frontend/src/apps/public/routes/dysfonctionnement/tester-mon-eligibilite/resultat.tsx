import React from "react";
import Breadcrumb from "@codegouvfr/react-dsfr/Breadcrumb";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { ButtonsGroup } from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Layout } from "@/apps/public/components/Layout";
import { REQUERANT_URL, usePublicNavigate } from "@/apps/public/routeur";
import { TOTAL_STEPS } from "@/apps/public/components/steps";
import { getCriteres, clearCriteres } from "@/apps/public/services/eligibiliteStore";
import { createFileRoute } from "@tanstack/react-router";

function ResultatEligibiliteRoute() {
  const navigate = usePublicNavigate();
  const criteres = getCriteres();
  const eligible = criteres.length > 0 && criteres.every((c) => c.rempli);

  return (
    <Layout>
      <Breadcrumb
        currentPageLabel="Résultat du test d'éligibilité"
        homeLinkProps={{ href: "/" }}
        segments={[
          {
            label: "Déclarer un déni de justice",
            linkProps: { to: "/dysfonctionnement/tester-mon-eligibilite/" },
          },
        ]}
      />

      <div
        className="fr-stepper fr-mb-2w"
        style={{ "--background-active-blue-france": "var(--background-flat-success)" } as React.CSSProperties}
      >
        <div
          className="fr-stepper__steps"
          data-fr-current-step={TOTAL_STEPS}
          data-fr-steps={TOTAL_STEPS}
        />
      </div>

      <h1>Résultat du test d'éligibilité</h1>
      <p className="fr-mb-3w">Synthèse de vos réponses et éligibilité préliminaire.</p>

      <div
        className="fr-mb-3w"
        style={{ border: "1px solid var(--border-default-grey)", borderRadius: "4px", overflow: "hidden" }}
      >
        {criteres.map(({ label, rempli, detail }, index) => (
          <div
            key={label}
            style={{
              display: "flex",
              alignItems: "flex-start",
              gap: "0.75rem",
              padding: "0.75rem 1rem",
              borderTop: index > 0 ? "1px solid var(--border-default-grey)" : undefined,
              backgroundColor: "var(--background-default-grey)",
            }}
          >
            <span
              className={
                rempli
                  ? "fr-icon-checkbox-circle-fill fr-text-default--success"
                  : "fr-icon-close-circle-fill fr-text-default--error"
              }
              aria-hidden="true"
              style={{ flexShrink: 0, marginTop: "2px" }}
            />
            <div>
              <strong style={{ color: "var(--text-default-grey)" }}>{label}</strong>
              <br />
              <span className={`fr-text--sm ${rempli ? "fr-text-default--success" : "fr-text-default--error"}`}>
                {detail}
              </span>
            </div>
          </div>
        ))}
      </div>

      {criteres.length > 0 && (
        <Alert
          className="fr-mb-3w"
          severity={eligible ? "success" : "error"}
          title={eligible ? "Demande éligible" : "Demande non éligible en l'état"}
          description={
            eligible
              ? "Votre demande semble recevable. Constituez votre dossier."
              : "Un ou plusieurs critères ne sont pas remplis. Consultez le détail ci-dessus."
          }
        />
      )}

      <ButtonsGroup
        inlineLayoutWhen="always"
        buttons={[
          {
            priority: "tertiary no outline",
            iconId: "fr-icon-arrow-left-line",
            iconPosition: "left",
            nativeButtonProps: { type: "button" },
            onClick: () => {
              clearCriteres();
              navigate({ to: "/dysfonctionnement/tester-mon-eligibilite/test-eligibilite" });
            },
            children: "Recommencer le test",
          },
          {
            linkProps: { href: REQUERANT_URL },
            children: "Déposer un dossier",
          },
        ]}
      />
    </Layout>
  );
}

export const Route = createFileRoute("/dysfonctionnement/tester-mon-eligibilite/resultat")({
  component: ResultatEligibiliteRoute,
});
