import React, { useState } from "react";
import Breadcrumb from "@codegouvfr/react-dsfr/Breadcrumb";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { Layout } from "./Layout";
import { TOTAL_STEPS } from "./EtapesEligibilite";

type Props = {
  onPrecedent: () => void;
  onCommencer: () => void;
};

export const TestEligibilite = ({ onPrecedent, onCommencer }: Props) => {
  const [procedureTerminee, setProcedureTerminee] = useState<boolean | undefined>(undefined);
  const [submitted, setSubmitted] = useState(false);

  const nonEligible = submitted && procedureTerminee === false;

  return (
    <Layout>
      <Breadcrumb
        currentPageLabel="Test d'éligibilité"
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

      <h1>Test d'éligibilité</h1>

      <p className="fr-mb-3w">
        Ce test vous permet de vérifier si vous pouvez faire une déclaration de déni de justice.
        Certaines conditions doivent être remplies, notamment que votre affaire soit définitivement
        terminée. Répondez aux questions suivantes pour continuer.
      </p>

      {nonEligible ? (
        <>
          <Alert
            className="fr-mb-3w"
            severity="error"
            title="Démarche non éligible"
            description={
              <>
                <p>Vous ne pouvez pas continuer le test d'éligibilité.</p>
                <p>
                  La déclaration de déni de justice n'est possible que si l'ensemble des procédures
                  liées à votre affaire est terminé (première instance, appel, etc.)
                </p>
                <p>
                  Vous pourrez effectuer cette démarche lorsque votre affaire sera définitivement
                  terminée.
                </p>
              </>
            }
          />
          <Button onClick={onPrecedent}>Retour à l'accueil</Button>
        </>
      ) : (
        <>
          <Stepper
            currentStep={1}
            stepCount={TOTAL_STEPS}
            title="État de la procédure"
            nextTitle="Date de la décision"
          />

          <RadioButtons
            legend="La procédure est-elle terminée ?"
            hintText="Vous ne pouvez faire une déclaration que si votre affaire est complètement terminée, c'est-à-dire après toutes les décisions de justice, y compris en appel si vous en avez fait un."
            options={[
              {
                label: "Oui",
                nativeInputProps: {
                  value: "oui",
                  checked: procedureTerminee === true,
                  onChange: () => setProcedureTerminee(true),
                },
              },
              {
                label: "Non",
                nativeInputProps: {
                  value: "non",
                  checked: procedureTerminee === false,
                  onChange: () => setProcedureTerminee(false),
                },
              },
            ]}
          />

          <Button
            className="fr-mt-2w"
            disabled={procedureTerminee === undefined}
            onClick={() => {
              if (procedureTerminee) onCommencer();
              else setSubmitted(true);
            }}
          >
            Suivant
          </Button>
        </>
      )}
    </Layout>
  );
};
