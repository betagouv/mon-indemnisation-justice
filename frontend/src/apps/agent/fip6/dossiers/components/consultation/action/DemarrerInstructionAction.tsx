import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import { DossierDetail } from "@common/models";
import { container } from "@fip6/container.ts";
import { AgentFIP6 } from "@fip6/modeles/AgentFIP6.ts";
import { DossierManagerInterface } from "@fip6/services/dossier.ts";

export const demarrerInstructionBoutons = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: AgentFIP6;
}): ButtonProps[] => {
  return dossier.enAttenteInstruction() && agent.instruit(dossier)
    ? [
        {
          children: "Démarrer l'instruction",
          priority: "primary",
          iconId: "fr-icon-play-line",
          onClick: async (e) => {
            (e.target as HTMLButtonElement).disabled = true;
            await container
              .get<DossierManagerInterface>(DossierManagerInterface.$)
              .demarrerInstruction(dossier);
            (e.target as HTMLButtonElement).disabled = false;
          },
        } as ButtonProps,
      ]
    : [];
};
