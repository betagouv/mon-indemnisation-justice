import React from "react";
import { ButtonsGroup } from "@codegouvfr/react-dsfr/ButtonsGroup";

type NavButtonsProps = {
  onPrecedent: () => void;
  isLastStep?: boolean;
  peutContinuer?: boolean;
};

export function NavButtons({ onPrecedent, isLastStep, peutContinuer = true }: NavButtonsProps) {
  return (
    <ButtonsGroup
      className="fr-mt-3w"
      inlineLayoutWhen="always"
      buttons={[
        {
          priority: "tertiary no outline",
          iconId: "fr-icon-arrow-left-line",
          iconPosition: "left",
          nativeButtonProps: { type: "button" },
          onClick: onPrecedent,
          children: "Retour",
        },
        ...(peutContinuer
          ? [
              {
                nativeButtonProps: { type: "submit" as const },
                children: isLastStep ? "Voir le résultat" : "Continuer",
              },
            ]
          : []),
      ]}
    />
  );
}
