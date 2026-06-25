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
import { container } from "@/apps/public/container";
import { TestEligibiliteManagerInterface } from "@/apps/public/services/TestEligibiliteManager";

function TestEligibiliteRoute() {
  const navigate = usePublicNavigate();
  const manager = useInjection<TestEligibiliteManagerInterface>(TestEligibiliteManagerInterface.$);
  const { test } = Route.useLoaderData();
  const [procedureTerminee, setProcedureTerminee] = useState<boolean | undefined>(
    () => test?.procedureTerminee,
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
       Ce test vous permet de vérifier si votre situation est susceptible de relever d’un déni de justice et de faire l’objet d’une demande de réparation.
       Répondez aux questions suivantes pour poursuivre votre démarche. 
      </p>

      {nonEligible ? (
        <>
          <Alert
            className="fr-mb-3w"
            severity="error"
            title="Vous ne pouvez pas poursuivre le test d’éligibilité."
            description={
              <>
                <p>La procédure concernée par votre demande est toujours en cours.</p>
                <p>
                  Une demande de réparation au titre d’un déni de justice ne peut être déposée qu’après que la juridiction concernée a statué.
                </p>
                <p>
                  Vous pourrez renouveler votre démarche une fois la décision rendue. 
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
            legend="La juridiction concernée a-t-elle rendu sa décision ?"
            hintText="Vous ne pouvez poursuivre ce test que si la juridiction dont vous souhaitez contester la durée de traitement a rendu sa décision. "
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
  loader: () => ({
    test: container.get<TestEligibiliteManagerInterface>(TestEligibiliteManagerInterface.$).get(),
  }),
});
