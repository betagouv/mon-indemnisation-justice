import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import { plainToInstance } from "class-transformer";
import { Agent, DossierDetail, EtatDossier } from "@/common/models";

const demarrerInstruction = async ({ dossier }: { dossier: DossierDetail }) => {
  const response = await fetch(
    `/api/agent/fip6/dossier/${dossier.id}/demarrer-instruction`,
    {
      method: "POST",
      headers: {
        "Content-type": "application/json",
        Accept: "application/json",
      },
    },
  );

  if (response.ok) {
    const data = await response.json();
    dossier.changerEtat(plainToInstance(EtatDossier, data.etat));
  }
};

export const demarrerInstructionBoutons = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: Agent;
}): ButtonProps[] => {
  return dossier.enAttenteInstruction() && agent.instruit(dossier)
    ? [
        {
          children: "Démarrer l'instruction",
          priority: "primary",
          iconId: "fr-icon-play-line",
          onClick: async (e) => {
            (e.target as HTMLButtonElement).disabled = true;
            await demarrerInstruction({ dossier });
          },
        } as ButtonProps,
      ]
    : [];
};
