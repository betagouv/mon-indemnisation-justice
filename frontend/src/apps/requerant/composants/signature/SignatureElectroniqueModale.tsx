import { Dossier } from "@/apps/requerant/models";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import React, { useState } from "react";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { Parcours, useParcours } from "@common/composants/dsfr/Parcours";
import { FormRadioButtons } from "@common/composants/dsfr/champs/form/FormRadioButtons.tsx";
import { useForm } from "@tanstack/react-form";
import { ContexteSignatureElectronique } from "@/apps/requerant/services/DossierManager.ts";
import { z } from "zod";
import { FormInput } from "@common/composants/dsfr/champs/form/FormInput.tsx";

const _modale = createModal({
  id: "modale-signature-electronique-dossier",
  isOpenedByDefault: false,
});

const EtapeIntro = ({
  montant,
  annuler,
  etapeSuivante,
}: {
  montant: string;
  annuler: () => void;
  etapeSuivante: () => void;
}) => (
  <>
    <p>
      Pour accepter la proposition d'indemnisation d'un montant de {montant} €
      qui vous est formulée, nous vous invitons à signer <i>électroniquement</i>{" "}
      la déclaration d'acceptation.
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

type ChoixRib = ContexteSignatureElectronique["rib"];

const normaliserIban = (iban: string): string =>
  iban.replace(/\s+/g, "").toUpperCase();

const schemaChoixRib = z.discriminatedUnion(
  "estAvocat",
  [
    z.object({
      estAvocat: z.literal(false),
      avocat: z.any(),
      rib: z.any(),
    }),
    z.object({
      estAvocat: z.literal(true),
      avocat: z
        .string({ error: "Le nom de l'avocat est requis" })
        .trim()
        .min(1, { error: "Le nom de l'avocat est requis" }),
      rib: z
        .string({ error: "L'IBAN de l'avocat est requis" })
        .trim()
        .min(1, { error: "L'IBAN de l'avocat est requis" })
        .refine(
          (iban) => /^[A-Z]{2}\d{2}[A-Z0-9]{11,30}$/.test(normaliserIban(iban)),
          { error: "L'IBAN saisi n'est pas valide" },
        ),
    }),
  ],
  {
    error: "Veuillez choisir le relevé d'identité bancaire à utiliser",
  },
);

const EtapeRib = ({
  rib,
  annuler,
  etapeSuivante,
}: {
  rib?: ChoixRib;
  annuler: () => void;
  etapeSuivante: (rib: ChoixRib) => void;
}) => {
  const formulaire = useForm({
    defaultValues: {
      estAvocat: rib?.estAvocat,
      avocat: rib?.avocat ?? "",
      rib: rib?.rib ?? "",
    } as { estAvocat?: boolean; avocat: string; rib: string },
    validators: {
      onSubmit: schemaChoixRib,
    },
    onSubmit: ({ value }) => {
      etapeSuivante(
        value.estAvocat
          ? {
              estAvocat: true,
              avocat: value.avocat.trim(),
              rib: normaliserIban(value.rib),
            }
          : { estAvocat: false },
      );
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
        name="estAvocat"
        children={(champ) => (
          <FormRadioButtons
            legend="Quel relevé d'identité bancaire souhaitez-vous utiliser pour le versement de l'indemnisation ?"
            estRequis={true}
            champ={champ}
            options={[
              {
                label: "Le mien, fourni lors de la constitution de mon dossier",
                nativeInputProps: {
                  value: "requerant",
                  checked: champ.state.value === false,
                  onChange: () => champ.handleChange(false),
                },
              },
              {
                label: "Celui de mon avocat",
                nativeInputProps: {
                  value: "avocat",
                  checked: champ.state.value === true,
                  onChange: () => champ.handleChange(true),
                },
              },
            ]}
          />
        )}
      />

      <formulaire.Subscribe
        selector={(state) => state.values.estAvocat}
        children={(estAvocat) =>
          estAvocat && (
            <>
              <formulaire.Field
                name="avocat"
                children={(champ) => (
                  <FormInput
                    label="Nom de l'avocat"
                    estRequis={true}
                    champ={champ}
                    nativeInputProps={{
                      maxLength: 255,
                      value: champ.state.value,
                      onChange: (e) => champ.handleChange(e.target.value),
                      onBlur: champ.handleBlur,
                    }}
                  />
                )}
              />

              <formulaire.Field
                name="rib"
                children={(champ) => (
                  <FormInput
                    label="IBAN de l'avocat"
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
            </>
          )
        }
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
  demarrer,
}: {
  sauvegardeEnCours: boolean;
  annuler: () => void;
  etapePrecedente: () => void;
  demarrer: () => Promise<void>;
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
          onClick: demarrer,
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
  const montant = "1245,87";
  const [sauvegardeEnCours, setSauvegardeEnCours] = useState<boolean>(false);
  const [rib, setRib] = useState<ChoixRib | undefined>(undefined);

  const parcours = useParcours({
    INTRO: {
      titre: "Accepter l'indemnisation",
      composant: ({ setEtape, suivante }) => (
        <EtapeIntro
          montant={montant}
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
          etapeSuivante={(rib) => {
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
          demarrer={async () => {
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
