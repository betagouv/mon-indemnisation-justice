import React from "react";
import Breadcrumb from "@codegouvfr/react-dsfr/Breadcrumb";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { Layout } from "./Layout";
import {
  ActionContentieuse,
  PreuvesDiligences,
  type ReponsesEligibilite,
  TypeDecision,
} from "./types";
import { calculerPrescription } from "./eligibilite.utils";

type Props = {
  reponses: ReponsesEligibilite;
  onRecommencer: () => void;
  onDeposerDossier: () => void;
  onPrecedent: () => void;
};

type Critere = {
  label: string;
  rempli: boolean;
  detail: string;
};

const buildCriteres = (reponses: ReponsesEligibilite): Critere[] => {
  const prescription = calculerPrescription(reponses.dateDecision);
  return [
    {
      label: "Date de la décision",
      rempli: prescription.rempli,
      detail: prescription.detail,
    },
    {
      label: "Action contentieuse",
      rempli: reponses.actionContentieuse === ActionContentieuse.Non,
      detail:
        reponses.actionContentieuse === ActionContentieuse.Non
          ? "Non, aucune action contentieuse"
          : reponses.actionContentieuse === ActionContentieuse.Oui
            ? "Oui, une procédure est en cours devant l'AJE"
            : "Non renseigné",
    },
    {
      label: "Décisions de justice",
      rempli: reponses.typeDecision !== undefined && reponses.typeDecision !== TypeDecision.Aucune,
      detail:
        reponses.typeDecision === TypeDecision.JugementPremiereInstance
          ? "Jugement de première instance"
          : reponses.typeDecision === TypeDecision.ArretCourAppel
            ? "Arrêt de la Cour d'appel"
            : reponses.typeDecision === TypeDecision.ArretCourCassation
              ? "Arrêt de la Cour de cassation"
              : reponses.typeDecision === TypeDecision.Aucune
                ? "Aucune décision"
                : "Non renseigné",
    },
    {
      label: "Pièces de procédure",
      rempli: reponses.piecesProc.length > 0,
      detail:
        reponses.piecesProc.length > 0
          ? `${reponses.piecesProc.length} pièce(s) sélectionnée(s)`
          : "Aucune pièce sélectionnée",
    },
    {
      label: "Diligences accomplies",
      rempli: reponses.preuvesDiligences === PreuvesDiligences.Oui,
      detail:
        reponses.preuvesDiligences === PreuvesDiligences.Oui
          ? "Oui, j'ai des preuves de mes démarches"
          : reponses.preuvesDiligences === PreuvesDiligences.Non
            ? "Non, je n'ai pas de traces écrites"
            : "Non renseigné",
    },
  ];
};

export const ResultatEligibilite = ({
  reponses,
  onRecommencer,
  onDeposerDossier,
  onPrecedent,
}: Props) => {
  const criteres = buildCriteres(reponses);
  const eligible = criteres.every((c) => c.rempli);

  return (
    <Layout>
      <Breadcrumb
        currentPageLabel="Résultat du test d'éligibilité"
        homeLinkProps={{ href: "/" }}
        segments={[
          {
            label: "Déclarer un déni de justice",
            linkProps: {
              href: "#",
              onClick: (e) => {
                e.preventDefault();
                onPrecedent();
              },
            },
          },
        ]}
      />

      <h1>Résultat du test d'éligibilité</h1>
      <p className="fr-mb-3w">Synthèse de vos réponses et éligibilité préliminaire.</p>

      <div
        className="fr-mb-3w"
        style={{
          border: "1px solid var(--border-default-grey)",
          borderRadius: "4px",
          overflow: "hidden",
        }}
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
              className={rempli ? "fr-icon-checkbox-circle-fill" : "fr-icon-close-circle-fill"}
              aria-hidden="true"
              style={{
                color: rempli ? "var(--text-default-success)" : "var(--text-default-error)",
                flexShrink: 0,
                marginTop: "2px",
              }}
            />
            <div>
              <strong style={{ color: "var(--text-default-grey)" }}>{label}</strong>
              <br />
              <span
                className="fr-text--sm"
                style={{
                  color: rempli ? "var(--text-default-success)" : "var(--text-default-error)",
                }}
              >
                {detail}
              </span>
            </div>
          </div>
        ))}
      </div>

      {eligible ? (
        <Alert
          severity="success"
          title="Demande éligible"
          description="Votre demande semble recevable. Constituez votre dossier."
        />
      ) : (
        <Alert
          severity="error"
          title="Demande non éligible en l'état"
          description="Un ou plusieurs critères ne sont pas remplis. Consultez le détail ci-dessus."
        />
      )}

      <div className="fr-mt-3w fr-btns-group fr-btns-group--inline">
        <Button
          priority="tertiary no outline"
          iconId="fr-icon-arrow-left-line"
          iconPosition="left"
          onClick={onRecommencer}
        >
          Recommencer le test
        </Button>
        <Button onClick={onDeposerDossier}>Déposer un dossier</Button>
      </div>
    </Layout>
  );
};
