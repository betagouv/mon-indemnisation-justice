import { Dossier } from "@/apps/requerant/models";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import React, { useState } from "react";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { Parcours, useParcours } from "@common/composants/dsfr/Parcours";
import { ContexteSignatureElectronique } from "@/apps/requerant/services/DossierManager.ts";
import { convertirEnEuros } from "@common/services/devise.ts";
import { type ReferenceBancaire } from "@/apps/requerant/models/ReferenceBancaire.ts";
import { EtapeReferenceBancaire } from "@/apps/requerant/composants/signature/EtapeReferenceBancaire.tsx";

const _modale = createModal({
  id: "modale-signature-electronique-dossier",
  isOpenedByDefault: false,
});

const EtapeIntro = ({
  montant,
  annuler,
  etapeSuivante,
}: {
  montant?: number;
  annuler: () => void;
  etapeSuivante: () => void;
}) => (
  <>
    <p>
      Pour accepter la proposition d'indemnisation{" "}
      {montant && <>d'un montant de {convertirEnEuros(montant)}</>} qui vous est
      formulée, nous vous invitons à signer <i>électroniquement</i> la
      déclaration d'acceptation.
    </p>

    <p>
      Pour cela nous avons besoin de régler quelques détails concernant le
      versement de l'indemnité.
    </p>

    <ButtonsGroup
      className="fr-mt-3w"
      buttonsSize="small"
      inlineLayoutWhen="always"
      alignment="right"
      buttons={[
        {
          children: "Annuler",
          priority: "tertiary no outline",
          onClick: annuler,
        },
        {
          children: "Avancer vers l'étape suivante",
          priority: "secondary",
          onClick: etapeSuivante,
        },
      ]}
    />
  </>
);

const EtapeConfirmation = ({
  sauvegardeEnCours,
  annuler,
  etapePrecedente,
  confirmer,
}: {
  sauvegardeEnCours: boolean;
  annuler: () => void;
  etapePrecedente: () => void;
  confirmer: () => Promise<void>;
}) => (
  <>
    <p>
      Le document est prêt pour être signé électroniquement via la plateforme de
      notre prestataire.
    </p>
    <p>
      En lançant la signature électronique, le document apparaîtra d'ici
      quelques instants sur cette page. Vous recevrez également un email de la
      part de notre prestataire vous invitant à le signer.
    </p>

    <p>
      Notre équipe sera notifiée dès que votre signature est confirmée afin de
      lancer la procédure de versement de l'indemnité.
    </p>

    <ButtonsGroup
      className="fr-mt-3w"
      buttonsSize="small"
      inlineLayoutWhen="always"
      alignment="right"
      buttons={[
        {
          children: "Annuler",
          priority: "tertiary no outline",
          disabled: sauvegardeEnCours,
          onClick: annuler,
        },
        {
          children: "Changer mes informations",
          priority: "secondary",
          disabled: sauvegardeEnCours,
          onClick: etapePrecedente,
        },
        {
          children: "Lancer la signature électronique",
          iconId: "fr-icon-edit-box-line",
          priority: "primary",
          disabled: sauvegardeEnCours,
          onClick: confirmer,
        },
      ]}
    />
  </>
);

export const SignatureElectroniqueModale = ({
  dossier,
  onDemarrer,
}: {
  dossier: Dossier;
  onDemarrer: (contexte: ContexteSignatureElectronique) => Promise<void>;
}) => {
  const [sauvegardeEnCours, setSauvegardeEnCours] = useState<boolean>(false);
  const [referenceBancaire, setReferenceBancaire] = useState<
    ReferenceBancaire | undefined
  >();

  const parcours = useParcours({
    INTRO: {
      titre: "Accepter l'indemnisation",
      composant: ({ setEtape, suivante }) => (
        <EtapeIntro
          montant={dossier.montantIndemnisation}
          annuler={() => {
            _modale.close();
            setEtape("INTRO");
          }}
          etapeSuivante={suivante}
        />
      ),
    },
    RIB: {
      titre: "Coordonnées bancaires",
      composant: ({ setEtape, suivante }) => (
        <EtapeReferenceBancaire
          dossier={dossier}
          referenceBancaire={referenceBancaire}
          annuler={() => {
            _modale.close();
            setEtape("INTRO");
          }}
          etapeSuivante={(referenceBancaire) => {
            setReferenceBancaire(referenceBancaire);
            suivante();
          }}
        />
      ),
    },
    CONFIRMATION: {
      titre: "Procéder à la signature électronique",
      composant: ({ precedente, setEtape }) => (
        <EtapeConfirmation
          sauvegardeEnCours={sauvegardeEnCours}
          annuler={() => {
            _modale.close();
            setEtape("INTRO");
          }}
          etapePrecedente={precedente}
          confirmer={async () => {
            if (!referenceBancaire) {
              setEtape("RIB");
              return;
            }

            setSauvegardeEnCours(true);
            await onDemarrer(referenceBancaire);
            setSauvegardeEnCours(false);
            _modale.close();
          }}
        />
      ),
    },
  });

  return (
    <>
      <ButtonsGroup
        className="fr-col-lg-3 fr-col-offset-lg-9"
        inlineLayoutWhen="always"
        alignment="right"
        buttons={[
          {
            children: "Accepter la proposition",
            // Une nouvelle signature peut remplacer l'actuelle une fois celle-ci fermée (expirée ou annulée)
            disabled:
              !!dossier.requeteSignature && !dossier.requeteSignature.estFermee,
            onClick: () => _modale.open(),
          },
        ]}
      />
      <_modale.Component
        title={"Accepter l'indemnisation"}
        size="large"
        concealingBackdrop={!sauvegardeEnCours}
      >
        <Parcours parcours={parcours} />
      </_modale.Component>
    </>
  );
};
