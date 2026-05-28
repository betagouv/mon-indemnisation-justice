import Breadcrumb from "@codegouvfr/react-dsfr/Breadcrumb";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { Layout } from "@/apps/public/components/Layout";
import { REQUERANT_URL } from "@/apps/public/routeur";
import { createFileRoute } from "@tanstack/react-router";

type Critere = {
  label: string;
  rempli: boolean;
  detail: string;
};

// TODO: récupérer les critères depuis la session PHP (option #2)
const CRITERES_STUB: Critere[] = [];

function ResultatEligibiliteRoute() {
  const criteres = CRITERES_STUB;
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
              className={`${rempli ? "fr-icon-checkbox-circle-fill fr-text-default--success" : "fr-icon-close-circle-fill fr-text-default--error"}`}
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
        <p className={`fr-text--lg ${eligible ? "fr-text-default--success" : "fr-text-default--error"}`}>
          {eligible ? "Votre demande semble recevable." : "Un ou plusieurs critères ne sont pas remplis."}
        </p>
      )}

      <div className="fr-mt-3w fr-btns-group fr-btns-group--inline">
        <Button
          priority="tertiary no outline"
          iconId="fr-icon-arrow-left-line"
          iconPosition="left"
          linkProps={{ to: "/dysfonctionnement/tester-mon-eligibilite/test-eligibilite" }}
        >
          Recommencer le test
        </Button>
        <Button linkProps={{ href: REQUERANT_URL }}>Déposer un dossier</Button>
      </div>
    </Layout>
  );
}

export const Route = createFileRoute("/dysfonctionnement/tester-mon-eligibilite/resultat")({
  component: ResultatEligibiliteRoute,
});
