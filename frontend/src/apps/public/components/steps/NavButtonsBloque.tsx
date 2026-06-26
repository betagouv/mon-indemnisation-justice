import React from "react";
import { ButtonsGroup } from "@codegouvfr/react-dsfr/ButtonsGroup";
import { usePublicNavigate } from "@/apps/public/routeur";
import { useInjection } from "inversify-react";
import { TestEligibiliteManagerInterface } from "@/apps/public/services/TestEligibiliteManager";
import { clearCriteres } from "@/apps/public/services/eligibiliteStore";

export function NavButtonsBloque() {
  const navigate = usePublicNavigate();
  const manager = useInjection<TestEligibiliteManagerInterface>(TestEligibiliteManagerInterface.$);

  const effacer = () => {
    manager.effacer();
    clearCriteres();
  };

  return (
    <ButtonsGroup
      className="fr-mt-3w"
      inlineLayoutWhen="always"
      alignment="right"
      buttons={[
        {
          nativeButtonProps: { type: "button" },
          onClick: () => { effacer(); navigate({ to: "/dysfonctionnement/tester-mon-eligibilite/" }); },
          children: "Retour à l'accueil",
        },
      ]}
    />
  );
}
