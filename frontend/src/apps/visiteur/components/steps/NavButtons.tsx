import React from "react";
import { Button } from "@codegouvfr/react-dsfr/Button";

type NavButtonsProps = {
  onPrecedent: () => void;
  isLastStep: boolean;
};

export function NavButtons({ onPrecedent, isLastStep }: NavButtonsProps) {
  return (
    <div className="fr-mt-3w fr-btns-group fr-btns-group--inline">
      <Button
        priority="tertiary no outline"
        iconId="fr-icon-arrow-left-line"
        iconPosition="left"
        nativeButtonProps={{ type: "button" }}
        onClick={onPrecedent}
      >
        Retour
      </Button>
      <Button nativeButtonProps={{ type: "submit" }}>
        {isLastStep ? "Voir le résultat" : "Continuer"}
      </Button>
    </div>
  );
}
