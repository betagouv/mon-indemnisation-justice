import { Alert } from "@codegouvfr/react-dsfr/Alert";
import Button, { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { FormInput } from "@common/composants/dsfr/champs/form/FormInput.tsx";
import { FormRadioButtons } from "@common/composants/dsfr/champs/form/FormRadioButtons.tsx";
import {
  Modale,
  ModaleProps,
  ModaleRef,
} from "@common/composants/dsfr/Modale.tsx";
import { DossierDetail } from "@common/models";
import { Document } from "@common/models/Document";
import { dsfr } from "@common/services/dsfr.ts";
import {
  ChampPieceJointe,
  TelechargerPieceJointe,
} from "@fip6/dossiers/components/consultation/piecejointe";
import { AgentFIP6 } from "@fip6/modeles/AgentFIP6.ts";
import { useForm } from "@tanstack/react-form";
import React, {
  forwardRef,
  type ReactNode,
  useCallback,
  useMemo,
  useState,
} from "react";
import { z } from "zod";

type VerificationPieceJointe = {
  pieceJointe: Document;
  verification: {
    estRecevable?: boolean;
    commentaire?: string;
  };
};

const schemaVerificationPieceJointe = z.object({
  estRecevable: z.boolean({
    error: "Veuillez indiquer si cette pièce jointe est recevable",
  }),
  commentaire: z.string().optional(),
});

const FormulaireVerifierPieceJointe = ({
  verification,
  boutonPrecedent,
  boutonSuivant,
}: {
  verification: VerificationPieceJointe;
  boutonPrecedent: {
    children: ReactNode;
    onClick?: React.MouseEventHandler<HTMLButtonElement>;
  };
  boutonSuivant: {
    children: ReactNode;
    onVerifie: (verification: VerificationPieceJointe) => void;
  };
}) => {
  const [afficherChampCommentaire, setAfficherChampCommentaire] =
    useState<boolean>(!!verification.verification.commentaire);

  const formulaire = useForm({
    defaultValues: {
      ...verification.verification,
    },
    validators: {
      onSubmit: schemaVerificationPieceJointe,
    },
    onSubmit: async ({ value }) => {
      boutonSuivant.onVerifie({
        pieceJointe: verification.pieceJointe,
        verification: value,
      });
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
      <div className="fr-grid-row fr-grid-row--gutters">
        <ButtonsGroup
          className="fr-col-12"
          inlineLayoutWhen="always"
          buttonsSize="small"
          alignment="right"
          buttons={[
            {
              children: boutonPrecedent.children,
              priority: "secondary",
              disabled: !boutonPrecedent.onClick,
              onClick: (e) => boutonPrecedent.onClick?.(e),
              nativeButtonProps: {
                type: "button",
              },
            },
            {
              children: boutonSuivant.children,
              priority: "primary",
              nativeButtonProps: {
                type: "submit",
              },
            },
          ]}
        />
        <div className="fr-grid-row fr-grid-row--gutters fr-mt-3w fr-col-12">
          <div className="fr-col-12 fr-col-lg-8 ">
            <TelechargerPieceJointe
              pieceJointe={verification.pieceJointe}
              className="fr-my-1w"
            />
            <ChampPieceJointe
              className="fr-my-1w"
              pieceJointe={verification.pieceJointe}
            />
          </div>

          <div className="fr-col-12 fr-col-lg-4">
            <h5>Validation</h5>
            <formulaire.Field
              name="estRecevable"
              children={(field) => (
                <FormRadioButtons
                  champ={field}
                  estRequis={true}
                  className="fr-my-0"
                  legend="Cette pièce jointe est recevable"
                  hintText="Le document est lisible et conforme à son type, les éléments nécessaires à l'instruction y sont apparents"
                  name={`recevable-${verification.pieceJointe.id}`}
                  options={[
                    {
                      label: "Oui",
                      nativeInputProps: {
                        checked: field.state.value === true,
                        onChange: () => field.handleChange(true),
                      },
                    },
                    {
                      label: "Non",
                      nativeInputProps: {
                        checked: field.state.value === false,
                        onChange: () => field.handleChange(false),
                      },
                    },
                  ]}
                />
              )}
            />

            <formulaire.Subscribe
              selector={(state) => ({
                estRecevable: state.values.estRecevable,
                commentaire: state.values.commentaire,
              })}
              children={({ estRecevable, commentaire }) => {
                const afficherCommentaire =
                  estRecevable === false ||
                  afficherChampCommentaire ||
                  !!commentaire;

                return (
                  <>
                    {!afficherCommentaire && (
                      <Button
                        title="Ajouter un commentaire"
                        iconId="fr-icon-chat-2-line"
                        priority="tertiary no outline"
                        nativeButtonProps={{
                          onClick: () => {
                            setAfficherChampCommentaire(true);
                          },
                        }}
                      >
                        Ajouter un commentaire
                      </Button>
                    )}

                    {afficherCommentaire && (
                      <formulaire.Field
                        name="commentaire"
                        children={(field) => (
                          <FormInput
                            champ={field}
                            estRequis={estRecevable === false}
                            label="Commentaire"
                            textArea={true}
                            nativeTextAreaProps={{
                              rows: 5,
                              value: field.state.value ?? "",
                              onChange: (e) =>
                                field.handleChange(e.target.value),
                            }}
                          />
                        )}
                      />
                    )}
                  </>
                );
              }}
            />
          </div>
        </div>
      </div>
    </form>
  );
};

type VerificationDossier = {
  dossier: DossierDetail;
  verification: {
    estRecevable?: boolean;
    commentaire?: string;
  };
  piecesJointes: VerificationPieceJointe[];
};

const schemaVerificationDossier = z
  .object({
    estRecevable: z.boolean({
      error: "Veuillez indiquer si ce dossier est complet",
    }),
    commentaire: z.string().optional(),
  })
  .refine((data) => data.estRecevable || !!data.commentaire?.trim(), {
    error: "Veuillez détailler les raisons de votre rejet",
    path: ["commentaire"],
  });

const FormulaireVerifierDossier = ({
  verification,
  boutonPrecedent,
  boutonSuivant,
  sauvegardeEnCours,
}: {
  verification: VerificationDossier["verification"];
  boutonPrecedent: {
    children: ReactNode;
    onClick?: React.MouseEventHandler<HTMLButtonElement>;
  };
  boutonSuivant: {
    children: ReactNode;
    onVerifie: (verification: VerificationDossier["verification"]) => void;
  };
  sauvegardeEnCours?: boolean;
}) => {
  const formulaire = useForm({
    defaultValues: {
      ...verification,
    },
    validators: {
      onSubmit: schemaVerificationDossier,
    },
    onSubmit: async ({ value }) => {
      boutonSuivant.onVerifie(value);
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
      <div className="fr-grid-row fr-grid-row--gutters">
        <formulaire.Field
          name="estRecevable"
          children={(field) => (
            <FormRadioButtons
              champ={field}
              estRequis={true}
              className="fr-col-12"
              orientation="horizontal"
              legend="Ce dossier est-il complet ?"
              hintText="Toutes les pièces jointes requises figurent bien au dossier"
              name="dossier-complet"
              disabled={sauvegardeEnCours}
              options={[
                {
                  label: "Oui",
                  hintText:
                    "L'instruction du dossier pourra débuter dès maintenant",
                  nativeInputProps: {
                    checked: field.state.value === true,
                    onChange: () => field.handleChange(true),
                  },
                },
                {
                  label: "Non",
                  hintText: "Le requérant sera invité à compléter son dossier",
                  nativeInputProps: {
                    checked: field.state.value === false,
                    onChange: () => field.handleChange(false),
                  },
                },
              ]}
            />
          )}
        />
        <formulaire.Subscribe
          selector={(state) => ({
            estRecevable: state.values.estRecevable,
          })}
          children={({ estRecevable }) => {
            return (
              <formulaire.Field
                name="commentaire"
                children={(field) => (
                  <FormInput
                    champ={field}
                    estRequis={estRecevable === false}
                    className="fr-col-12 fr-mb-3w"
                    label="Explications détaillées à desination du requérant"
                    hintText="Indiquez-lui ce qu'il manque à son dossier pour qu'il soit complet"
                    textArea={true}
                    disabled={sauvegardeEnCours}
                    nativeTextAreaProps={{
                      rows: 5,
                      value: field.state.value ?? "",
                      onChange: (e) => field.handleChange(e.target.value),
                    }}
                  />
                )}
              />
            );
          }}
        />
      </div>

      <ButtonsGroup
        className="fr-col-12"
        inlineLayoutWhen="always"
        buttonsSize="small"
        alignment="right"
        buttons={[
          {
            children: boutonPrecedent.children,
            priority: sauvegardeEnCours ? "tertiary no outline" : "secondary",
            disabled: sauvegardeEnCours || !boutonPrecedent.onClick,
            onClick: (e) => boutonPrecedent.onClick?.(e),
            nativeButtonProps: {
              type: "button",
            },
          },
          {
            children: sauvegardeEnCours
              ? "Enregistrement..."
              : boutonSuivant.children,
            priority: "primary",
            disabled: sauvegardeEnCours,
            nativeButtonProps: {
              type: "submit",
            },
          },
        ]}
      />
    </form>
  );
};

type VerifierDossierActionModaleProps = Omit<
  ModaleProps,
  "children" | "id" | "title" | "titleAs" | "titleProps"
> & {
  dossier: DossierDetail;
  agent: AgentFIP6;
};

export const VerifierDossierActionModale = forwardRef<
  ModaleRef,
  VerifierDossierActionModaleProps
>(({ dossier, agent, ...props }, ref) => {
  const [etape, setEtape] = useState<"pieces_jointes" | "conclusion">(
    "pieces_jointes",
  );
  const [verification, setVerification] = useState<VerificationDossier>({
    dossier,
    verification: {
      estRecevable: undefined,
      commentaire: undefined,
    },
    piecesJointes: dossier.piecesJointes.map((pieceJointe: Document) => ({
      pieceJointe,
      verification: {
        estRecevable: undefined,
        commentaire: undefined,
      },
    })),
  });
  const [sauvegardeEnCours, setSauvegardeEnCours] = useState<boolean>(false);
  const [index, setIndex] = useState<number>(0);
  const pieceJointeInspectee = useMemo(
    () => dossier.piecesJointes.at(index),
    [dossier, index],
  );
  const estDernierePieceJointe = useCallback(
    () => index == dossier.piecesJointes.length - 1,
    [dossier.id, index],
  );

  const pieceJointePrecedente = () => {
    if (index > 0) {
      setIndex(index - 1);
    }
  };

  const pieceJointeSuivante = () => {
    if (index < dossier.piecesJointes.length - 1) {
      setIndex(index + 1);
    }
  };

  return (
    <Modale
      title={`Vérifier le dossier ${dossier.reference}`}
      id="verifier-dossier-modale"
      size={etape === "pieces_jointes" ? "full" : "large"}
      onFerme={() => setEtape("pieces_jointes")}
      refermable={!sauvegardeEnCours}
      concealingBackdrop={!sauvegardeEnCours}
      {...props}
    >
      {etape === "pieces_jointes" && (
        <>
          <h6>Inspection des pièces jointes</h6>
          {pieceJointeInspectee ? (
            <>
              <Stepper
                currentStep={index + 1}
                stepCount={dossier.piecesJointes.length}
                title={`Pièce jointe n°${index + 1} : ${pieceJointeInspectee.originalFilename} (${pieceJointeInspectee.type.libelle})`}
              />
              <FormulaireVerifierPieceJointe
                key={pieceJointeInspectee.id}
                verification={
                  verification.piecesJointes.find(
                    (v) => v.pieceJointe.id == pieceJointeInspectee.id,
                  ) as VerificationPieceJointe
                }
                boutonPrecedent={{
                  children: "Pièce jointe précédente",
                  onClick:
                    index > 0 ? () => pieceJointePrecedente() : undefined,
                }}
                boutonSuivant={{
                  children: estDernierePieceJointe()
                    ? "Rédiger la synthèse"
                    : "Pièce jointe suivante",
                  onVerifie: estDernierePieceJointe()
                    ? (vpj: VerificationPieceJointe) => setEtape("conclusion")
                    : (vpj: VerificationPieceJointe) => {
                        pieceJointeSuivante();
                        setVerification((v) => ({
                          ...v,
                          piecesJointes: v.piecesJointes.map((pj) =>
                            pj.pieceJointe.id == vpj.pieceJointe.id ? vpj : pj,
                          ),
                        }));
                      },
                }}
              />
            </>
          ) : (
            <Alert
              severity="warning"
              title="Ce dossier est vide"
              description={<p>Il n'y a aucune pièce associée à ce dossier.</p>}
            />
          )}
        </>
      )}

      {etape === "conclusion" && (
        <>
          <h6>Synthèse</h6>

          <FormulaireVerifierDossier
            verification={verification.verification}
            sauvegardeEnCours={sauvegardeEnCours}
            boutonPrecedent={{
              children: "Revoir les pièces jointes",
              onClick: () => {
                setIndex(0);
                setEtape("pieces_jointes");
              },
            }}
            boutonSuivant={{
              children: "Envoyer",
              onVerifie: (v) => {
                setVerification((prev) => ({ ...prev, verification: v }));
                setSauvegardeEnCours(true);
                setTimeout(() => {
                  dsfr(document.getElementById("verifier-dossier-modale"))
                    .then((dsfr) => {
                      dsfr?.modal.conceal();
                      setSauvegardeEnCours(false);
                      setEtape("pieces_jointes");
                      setIndex(0);
                    })
                    .catch((e) => console.error(e));
                }, 5000);
              },
            }}
          />
        </>
      )}
    </Modale>
  );
});

export const verifierBoutons = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: AgentFIP6;
}): ButtonProps[] => {
  return dossier.estAVerifier
    ? [
        {
          children: "Vérifier le dossier",
          priority: "primary",
          iconId: "fr-icon-check-line",
          onClick: () =>
            dsfr(document.getElementById("verifier-dossier-modale"))
              .then((dsfr) => dsfr?.modal.disclose())
              .catch((e) => console.error(e)),
        } as ButtonProps,
      ]
    : [];
};
