import React from "react";
import { ConditionalButtonsGroup } from "./ConditionalButtonsGroup";

type NavButtonsProps = {
  onPrecedent?: () => void;
  onAnnuler?: () => void;
  isLastStep?: boolean;
  peutContinuer?: boolean;
};

export function NavButtons({ onPrecedent, onAnnuler, isLastStep, peutContinuer = true }: NavButtonsProps) {
  return (
    <ConditionalButtonsGroup
      className="fr-mt-3w"
      inlineLayoutWhen="always"
      alignment="right"
      buttons={[
        { button: { priority: "tertiary no outline", nativeButtonProps: { type: "button" }, onClick: onAnnuler, children: "Annuler" }, condition: !!onAnnuler },
        { button: { priority: "secondary", iconId: "fr-icon-arrow-left-line", iconPosition: "left", nativeButtonProps: { type: "button" }, onClick: onPrecedent, children: "Précédent" }, condition: !!onPrecedent },
        { button: { nativeButtonProps: { type: "submit" }, children: isLastStep ? "Voir le résultat" : "Étape suivante" }, condition: peutContinuer },
      ]}
    />
  );
}
