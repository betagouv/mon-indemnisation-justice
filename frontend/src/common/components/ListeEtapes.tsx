import React, { ReactNode } from "react";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";

export const ListeEtapes = function <T extends string>({
  etapeActuelle,
  etapes,
  etapesSuivantes,
}: {
  etapeActuelle: T;
  etapes: Record<T, { titre: string; component: ReactNode }>;
  etapesSuivantes?: [T];
}) {
  return (
    <>
      <Stepper
        currentStep={etapeActuelle.indexOf(etapeActuelle) + 1}
        stepCount={
          etapesSuivantes
            ? etapeActuelle.indexOf(etapeActuelle) + etapesSuivantes.length
            : etapeActuelle.length
        }
        title={etapes[etapeActuelle].titre}
      />

      {etapes[etapeActuelle].component}
    </>
  );
};
