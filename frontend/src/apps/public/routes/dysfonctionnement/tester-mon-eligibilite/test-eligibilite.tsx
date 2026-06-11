import Breadcrumb from "@codegouvfr/react-dsfr/Breadcrumb";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { Layout } from "@/apps/public/components/Layout";
import { usePublicNavigate } from "@/apps/public/routeur";
import { TOTAL_STEPS } from "@/apps/public/components/steps";
import { createFileRoute } from "@tanstack/react-router";
import React, { useState } from "react";
import { saveCritere, critereProcedureTerminee, clearCriteres } from "@/apps/public/services/eligibiliteStore";
import { useInjection } from "inversify-react";
import { TestEligibiliteManagerInterface } from "@/apps/public/services/TestEligibiliteManager";

function TestEligibiliteRoute() {
  const navigate = usePublicNavigate();
  const manager = useInjection<TestEligibiliteManagerInterface>(TestEligibiliteManagerInterface.$);
  const [procedureTerminee, setProcedureTerminee] = useState<boolean | undefined>(
    () => manager.get()?.procedureTerminee,
  );
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
            linkProps: { to: "/dysfonctionnement/tester-mon-eligibilite/" },
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
          <Button
            onClick={() => {
              manager.effacer();
              clearCriteres();
              navigate({ to: "/dysfonctionnement/tester-mon-eligibilite/" });
            }}
          >
            Retour à l'accueil
          </Button>
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
              manager.get() ?? manager.creer();
              manager.modifier({ procedureTerminee: procedureTerminee! });
              if (procedureTerminee) {
                saveCritere("procedureTerminee", critereProcedureTerminee());
                navigate({ to: "/dysfonctionnement/tester-mon-eligibilite/1-date-decision" });
              } else {
                setSubmitted(true);
              }
            }}
          >
            Suivant
          </Button>
        </>
      )}
    </Layout>
  );
}

export const Route = createFileRoute("/dysfonctionnement/tester-mon-eligibilite/test-eligibilite")({
  component: TestEligibiliteRoute,
});
