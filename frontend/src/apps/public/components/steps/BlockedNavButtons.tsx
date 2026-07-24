import React from "react";
import { ConditionalButtonsGroup } from "./ConditionalButtonsGroup";

type BlockedNavButtonsProps = {
  onRetour: () => void;
};

export function BlockedNavButtons({ onRetour }: BlockedNavButtonsProps) {
  return (
    <ConditionalButtonsGroup
      className="fr-mt-3w"
      inlineLayoutWhen="always"
      alignment="right"
      buttons={[
        {
          nativeButtonProps: { type: "button" },
          onClick: onRetour,
          children: "Retour à l'accueil",
        },
      ]}
    />
  );
}
