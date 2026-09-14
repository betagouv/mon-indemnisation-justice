import {
  criterePrescription,
  saveCritere,
} from "@/apps/public/services/eligibiliteStore";
import {
  calculerDateFin,
  calculerPrescription,
} from "@/apps/public/services/prescription";
import { TestEligibiliteManagerInterface } from "@/apps/public/services/TestEligibiliteManager";
import { dateChiffre, dateSimple } from "@/common/services/date";
import { Accordion } from "@codegouvfr/react-dsfr/Accordion";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { CheckInput } from "@common/composants/dsfr/champs/check/CheckInput.tsx";
import { useForm, useStore } from "@tanstack/react-form";
import { useInjection } from "inversify-react";
import React, { useMemo } from "react";
import { SchemaEtapeDateDecision } from "../formulaires/eligibilite.schemas";
import type { StepProps } from "../types";
import { BlockedNavButtons } from "./BlockedNavButtons";
import { NavButtons } from "./NavButtons";

export function StepDateDecision({
  onPrecedent,
  onSuivant,
  onAnnuler,
  onRetour,
  isLastStep,
  test,
}: StepProps) {
  const manager = useInjection<TestEligibiliteManagerInterface>(
    TestEligibiliteManagerInterface.$,
  );
  const annee = new Date().getFullYear() - 3;

  const formulaire = useForm({
    validators: { onSubmit: SchemaEtapeDateDecision },
    defaultValues: { dateDecision: test?.dateDecision },
    onSubmit: async ({ value, formApi }) => {
      if (formApi.state.isValid) {
        const critere = criterePrescription(value.dateDecision as Date);
        if (!critere.rempli) return;
        manager.modifier({
          dateDecision: value.dateDecision as unknown as Date,
        });
        saveCritere("prescription", critere);
        onSuivant();
      }
    },
  });

  const dateDecision = useStore(
    formulaire.store,
    (state) => state.values.dateDecision,
  );

  const datePrescription = useMemo(
    () => (dateDecision ? calculerPrescription(dateDecision) : undefined),
    [dateDecision],
  );

  const estRecevable = useMemo<boolean | undefined>(
    () => (datePrescription ? new Date() < datePrescription : undefined),
    [datePrescription],
  );

  const dateFin = useMemo(
    () => (datePrescription ? calculerDateFin(datePrescription) : undefined),
    [dateDecision],
  );

  return (
    <form
      onSubmit={async (e) => {
        e.preventDefault();
        e.stopPropagation();
        await formulaire.handleSubmit();
      }}
    >
      <div className="fr-grid-row">
        <div className="fr-col-12 fr-col-lg-3">
          <formulaire.Field
            name="dateDecision"
            children={(field) => (
              <CheckInput
                label="Date de la décision"
                hintText="Indiquez la date de la décision rendue par la juridiction concernée par votre demande."
                validation={false}
                nativeInputProps={{
                  type: "date",
                  defaultValue: dateChiffre(field.state.value),
                  onChange: (e) => {
                    const date = new Date(e.target.value);

                    if (!isNaN(date.getTime())) {
                      field.setValue(date);
                    }
                  },
                }}
              />
            )}
          />
        </div>
      </div>

      <formulaire.Subscribe
        selector={(state) => ({
          dateDecision: state.values.dateDecision,
          showError: state.submissionAttempts > 0,
        })}
        children={({ dateDecision, showError }) => {
          if (showError && !dateDecision) {
            return (
              <Alert
                className="fr-mt-2w"
                severity="error"
                title="Veuillez indiquer la date de la décision"
              />
            );
          }
          if (dateDecision) {
            return (
              <Alert
                className="fr-mt-2w"
                severity={estRecevable ? "success" : "error"}
                title={
                  estRecevable
                    ? "Votre demande est recevable"
                    : "Votre demande est prescrite"
                }
                description={
                  estRecevable
                    ? `Vous pouvez présenter votre demande jusqu’au ${dateSimple(dateFin as Date)} inclus.`
                    : `Le délai pour présenter votre demande a expiré le  ${dateSimple(datePrescription as Date)}.`
                }
              />
            );
          }
          return null;
        }}
      />

      <p
        className="fr-text--sm fr-mt-2w"
        style={{ color: "var(--text-default-info)" }}
      >
        <span
          className="fr-icon-information-line fr-icon--sm"
          aria-hidden="true"
        />{" "}
        La prescription est quadriennale : vous disposez de 4 ans à compter du
        1er janvier de l’année suivant celle où la décision a été rendue pour
        présenter votre demande d’indemnisation.
      </p>

      <Accordion
        label="Référence juridique et exemple"
        className="fr-mt-2w fr-mb-2w"
      >
        <div className="fr-callout">
          <p className="fr-text--sm">
            Référence juridique : Art. 1er, loi n°68-1250 du 31/12/1968 — Civ.
            1re, 15/06/2017, n°16-18.769
          </p>
          <p className="fr-text--sm fr-mb-0">
            Exemple : Décision rendue le 15 mars {annee} → délai du 1er janvier{" "}
            {annee + 1} au 31 décembre {annee + 4}. Au 1er janvier {annee + 5},
            l'action est prescrite.
          </p>
        </div>
      </Accordion>

      {dateDecision && !estRecevable && onRetour ? (
        <BlockedNavButtons onRetour={onRetour} />
      ) : (
        <NavButtons
          onPrecedent={onPrecedent}
          onAnnuler={onAnnuler}
          isLastStep={isLastStep}
          peutContinuer={estRecevable}
        />
      )}
    </form>
  );
}
