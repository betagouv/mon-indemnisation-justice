import { Input } from "@codegouvfr/react-dsfr/Input";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { calculerPrescription } from "../eligibilite.utils";
import type { StepProps } from "../types";

const ReferenceJuridique = (
  <div
    className="fr-text--sm fr-mb-2w fr-p-2w"
    style={{
      border: "1px solid var(--border-default-grey)",
      backgroundColor: "var(--background-alt-grey)",
      color: "var(--text-default-grey)",
    }}
  >
    Art. 1er, loi n°68-1250 du 31/12/1968 — Civ. 1re, 15/06/2017, n°16-18.769
  </div>
);

const ExemplePrescription = (
  <div className="fr-callout fr-mb-3w">
    <p className="fr-text--sm fr-mb-0">
      Décision rendue le 15 mars 2021 → délai du 1er janvier 2022 au 31 décembre 2025. Au
      1er janvier 2026, l'action est prescrite.
    </p>
  </div>
);

export function StepDateDecision({ reponses, set }: StepProps) {
  const prescription = reponses.dateDecision
    ? calculerPrescription(reponses.dateDecision)
    : null;

  return (
    <>
      <p className="fr-text--sm fr-mb-2w">
        La prescription est quadriennale : vous disposez de 4 ans à compter du 1er janvier de
        l'année suivant celle de la décision pour agir.
      </p>
      {ReferenceJuridique}
      {ExemplePrescription}
      <div style={{ width: "25%" }}>
        <Input
          label="Date de la décision"
          hintText="Indiquez la date de la dernière décision de justice rendue dans votre affaire."
          nativeInputProps={{
            type: "date",
            value: reponses.dateDecision ?? "",
            onChange: (e) => set("dateDecision", e.target.value || undefined),
          }}
        />
      </div>
      {prescription && (
        <Alert
          className="fr-mt-2w"
          severity={prescription.rempli ? "success" : "error"}
          title={prescription.rempli ? "Vous êtes dans les délais" : "Critère non rempli"}
          description={
            prescription.rempli
              ? `Votre demande est recevable jusqu'au ${prescription.expiration?.toLocaleDateString("fr-FR")}.`
              : prescription.expiration
                ? `Votre demande est prescrite depuis le ${prescription.expiration.toLocaleDateString("fr-FR")}. L'action en responsabilité de l'État ne peut plus être engagée passé le délai de 4 ans.`
                : "Date invalide. Veuillez vérifier la date saisie."
          }
        />
      )}
    </>
  );
}
