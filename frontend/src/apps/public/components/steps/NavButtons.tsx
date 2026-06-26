import React from "react";
import { ButtonsGroup } from "@codegouvfr/react-dsfr/ButtonsGroup";

type NavButtonsProps = {
  onPrecedent?: () => void;
  onAnnuler?: () => void;
  isLastStep?: boolean;
  peutContinuer?: boolean;
};

export function NavButtons({ onPrecedent, onAnnuler, isLastStep, peutContinuer = true }: NavButtonsProps) {
  type Btn = React.ComponentProps<typeof ButtonsGroup>["buttons"][number];

  const buttons: Btn[] = [
    ...(onAnnuler
      ? [{ priority: "tertiary no outline" as const, nativeButtonProps: { type: "button" as const }, onClick: onAnnuler, children: "Annuler" }]
      : []),
    ...(onPrecedent
      ? [{ priority: "secondary" as const, iconId: "fr-icon-arrow-left-line" as const, iconPosition: "left" as const, nativeButtonProps: { type: "button" as const }, onClick: onPrecedent, children: "Précédent" }]
      : []),
    ...(peutContinuer
      ? [{ nativeButtonProps: { type: "submit" as const }, children: isLastStep ? "Voir le résultat" : "Étape suivante" }]
      : []),
  ];

  return (
    <ButtonsGroup
      className="fr-mt-3w"
      inlineLayoutWhen="always"
      alignment="right"
      buttons={buttons as [Btn, ...Btn[]]}
    />
  );
}
