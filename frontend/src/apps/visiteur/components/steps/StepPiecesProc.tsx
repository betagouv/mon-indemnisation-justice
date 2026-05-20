import React from "react";
import { useForm } from "@tanstack/react-form";
import Checkbox from "@codegouvfr/react-dsfr/Checkbox";
import { PieceProcedure } from "../types";
import { SchemaEtapePiecesProc } from "../formulaires/eligibilite.schemas";
import type { StepProps } from "../types";
import { NavButtons } from "./NavButtons";

export function StepPiecesProc({ reponses, onPrecedent, onSuivant, isLastStep }: StepProps) {
  const formulaire = useForm({
    validators: { onSubmit: SchemaEtapePiecesProc },
    defaultValues: { piecesProc: reponses.piecesProc },
    onSubmit: async ({ value, formApi }) => {
      if (formApi.state.isValid) {
        onSuivant({ piecesProc: value.piecesProc as PieceProcedure[] });
      }
    },
  });

  return (
    <form
      onSubmit={async (e) => {
        e.preventDefault();
        e.stopPropagation();
        formulaire.validate("submit");
        await formulaire.handleSubmit();
      }}
    >
      <formulaire.Field
        name="piecesProc"
        children={(field) => {
          const toggle = (piece: PieceProcedure) => {
            const val = field.state.value;
            field.handleChange(
              val.includes(piece) ? val.filter((p) => p !== piece) : [...val, piece],
            );
          };

          return (
            <Checkbox
              legend="De quelles pièces de la procédure disposez-vous ?"
              hintText="Pour qualifier le délai déraisonnable, l'ensemble des décisions rendues dans votre procédure est nécessaire."
              state={!field.state.meta.isValid ? "error" : "default"}
              stateRelatedMessage={!field.state.meta.isValid ? (field.state.meta.errors.at(0)?.message ?? "") : ""}
              options={[
                {
                  label: "Assignation ou requête introductive",
                  nativeInputProps: {
                    checked: field.state.value.includes(PieceProcedure.Assignation),
                    onChange: () => toggle(PieceProcedure.Assignation),
                  },
                },
                {
                  label: "Décisions du juge de la mise en état",
                  nativeInputProps: {
                    checked: field.state.value.includes(PieceProcedure.DecisionsJuge),
                    onChange: () => toggle(PieceProcedure.DecisionsJuge),
                  },
                },
                {
                  label: "Calendrier de procédure",
                  nativeInputProps: {
                    checked: field.state.value.includes(PieceProcedure.Calendrier),
                    onChange: () => toggle(PieceProcedure.Calendrier),
                  },
                },
                {
                  label: "Écritures des parties",
                  nativeInputProps: {
                    checked: field.state.value.includes(PieceProcedure.Ecritures),
                    onChange: () => toggle(PieceProcedure.Ecritures),
                  },
                },
                {
                  label: "Convocation aux audiences",
                  nativeInputProps: {
                    checked: field.state.value.includes(PieceProcedure.Convocations),
                    onChange: () => toggle(PieceProcedure.Convocations),
                  },
                },
                {
                  label: "Décision de renvoi",
                  nativeInputProps: {
                    checked: field.state.value.includes(PieceProcedure.Renvoi),
                    onChange: () => toggle(PieceProcedure.Renvoi),
                  },
                },
                {
                  label: "Échanges avec la juridiction",
                  nativeInputProps: {
                    checked: field.state.value.includes(PieceProcedure.Echanges),
                    onChange: () => toggle(PieceProcedure.Echanges),
                  },
                },
                {
                  label: "Déclaration d'appel",
                  nativeInputProps: {
                    checked: field.state.value.includes(PieceProcedure.Appel),
                    onChange: () => toggle(PieceProcedure.Appel),
                  },
                },
              ]}
            />
          );
        }}
      />
      <NavButtons onPrecedent={onPrecedent} isLastStep={isLastStep} />
    </form>
  );
}
