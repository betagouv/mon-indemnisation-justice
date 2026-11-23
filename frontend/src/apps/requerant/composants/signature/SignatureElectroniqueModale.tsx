import { Dossier } from "@/apps/requerant/models";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import React, { useState } from "react";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { Parcours, useParcours } from "@common/composants/dsfr/Parcours";
import { useForm } from "@tanstack/react-form";
import { ContexteSignatureElectronique } from "@/apps/requerant/services/DossierManager.ts";
import { z } from "zod";
import { FormInput } from "@common/composants/dsfr/champs/form/FormInput.tsx";
import { convertirEnEuros } from "@common/services/devise.ts";

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

const normaliserIban = (iban: string): string =>
  iban.replace(/\s+/g, "").toUpperCase();

const schemaChoixRib = z.object({
  rib: z
    .string({ error: "L'IBAN de l'avocat est requis" })
    .trim()
    .min(1, { error: "L'IBAN de l'avocat est requis" })
    .refine(
      (iban) => /^[A-Z]{2}\d{2}[A-Z0-9]{11,30}$/.test(normaliserIban(iban)),
      { error: "L'IBAN saisi n'est pas valide" },
    ),
});

const EtapeRib = ({
  rib,
  annuler,
  etapeSuivante,
}: {
  rib: string;
  annuler: () => void;
  etapeSuivante: ({ rib }: { rib: string }) => void;
}) => {
  const formulaire = useForm({
    defaultValues: {
      rib,
    },
    validators: {
      onSubmit: schemaChoixRib,
    },
    onSubmit: ({ value }) => {
      etapeSuivante({ rib: normaliserIban(value.rib) });
    },
  });

  return (
    <form
      onSubmit={(e) => {
        e.preventDefault();
        e.stopPropagation();
        void formulaire.handleSubmit();
      }}
    >
      <formulaire.Field
        name="rib"
        children={(champ) => (
          <FormInput
            label="RIB / IBAN "
            hintText="Exemple : FR76 3000 6000 0112 3456 7890 189"
            estRequis={true}
            champ={champ}
            nativeInputProps={{
              maxLength: 42,
              autoComplete: "off",
              value: champ.state.value,
              onChange: (e) => champ.handleChange(e.target.value),
              onBlur: champ.handleBlur,
            }}
          />
        )}
      />

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
            nativeButtonProps: {
              role: "submit",
              type: "submit",
            },
          },
        ]}
      />
    </form>
  );
};

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
  const montant = 1245.87;
  const [sauvegardeEnCours, setSauvegardeEnCours] = useState<boolean>(false);
  const [rib, setRib] = useState<string>("");

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
      titre: "Choix du relevé d'identité bancaire",
      composant: ({ setEtape, suivante }) => (
        <EtapeRib
          rib={rib}
          annuler={() => {
            _modale.close();
            setEtape("INTRO");
          }}
          etapeSuivante={({ rib }) => {
            setRib(rib);
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
            if (!rib) {
              setEtape("RIB");
              return;
            }

            setSauvegardeEnCours(true);
            await onDemarrer({ rib });
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
