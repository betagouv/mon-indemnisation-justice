import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Input } from "@codegouvfr/react-dsfr/Input";
import RadioButtons from "@codegouvfr/react-dsfr/RadioButtons";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import {
  Modale,
  ModaleProps,
  ModaleRef,
} from "@common/composants/dsfr/Modale.tsx";
import { DossierDetail } from "@common/models";
import { dsfr } from "@common/services/dsfr.ts";
import {
  ChampPieceJointe,
  TelechargerPieceJointe,
} from "@fip6/dossiers/components/consultation/piecejointe";
import { AgentFIP6 } from "@fip6/modeles/AgentFIP6.ts";
import React, { forwardRef, useCallback, useMemo, useState } from "react";

export type VerifierDossierActionModaleProps = Omit<
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

              <div className="fr-grid-row fr-grid-row--gutters">
                <ButtonsGroup
                  className="fr-col-12"
                  inlineLayoutWhen="always"
                  buttonsSize="small"
                  alignment="right"
                  buttons={[
                    {
                      children: "Pièce jointe précédente",
                      priority: "secondary",
                      disabled: index == 0,
                      onClick: () => pieceJointePrecedente(),
                    },
                    ...(estDernierePieceJointe()
                      ? ([
                          {
                            children: "Rédiger la synthèse",
                            priority: "primary",
                            onClick: () => setEtape("conclusion"),
                          },
                        ] as ButtonProps[])
                      : ([
                          {
                            children: "Pièce jointe suivante",
                            priority: "primary",
                            onClick: () => pieceJointeSuivante(),
                          },
                        ] as ButtonProps[])),
                  ]}
                />

                <div className="fr-grid-row fr-grid-row--gutters fr-mt-3w fr-col-12">
                  <div className="fr-col-12 fr-col-lg-8">
                    <TelechargerPieceJointe
                      pieceJointe={pieceJointeInspectee}
                      className="fr-my-1w"
                    />
                    <ChampPieceJointe
                      className="fr-my-1w"
                      pieceJointe={pieceJointeInspectee}
                    />
                  </div>

                  <div className="fr-col-12 fr-col-lg-4">
                    <h5>Validation</h5>
                    <RadioButtons
                      legend="Cette pièce jointe est recevable"
                      hintText="Le document est lisible et conforme à son type, les éléments nécessaires à l'instruction y sont apparents"
                      name="radio"
                      options={[
                        {
                          label: "Oui",
                          nativeInputProps: {
                            checked: false,
                            onChange: () =>
                              console.log(
                                `Pièce jointe ${pieceJointeInspectee.id} recevable ?`,
                                true,
                              ),
                          },
                        },
                        {
                          label: "Non",
                          nativeInputProps: {
                            checked: false,
                            onChange: () =>
                              console.log(
                                `Pièce jointe ${pieceJointeInspectee.id} recevable ?`,
                                false,
                              ),
                          },
                        },
                      ]}
                    />

                    <Input
                      label="Commentaire"
                      textArea={true}
                      nativeTextAreaProps={{
                        rows: 5,
                      }}
                    />
                  </div>
                </div>
              </div>
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

          <div className="fr-grid-row fr-grid-row--gutters">
            <RadioButtons
              className="fr-col-12"
              orientation="horizontal"
              legend="Ce dossier est-il complet ?"
              hintText="Toutes les pièces jointes requises figurent bien au dossier"
              name="radio"
              disabled={sauvegardeEnCours}
              options={[
                {
                  label: "Oui",
                  hintText:
                    "L'instruction du dossier pourra débuter dès maintenant",
                  nativeInputProps: {
                    checked: false,
                    onChange: () => console.log("Dossier complet ?", true),
                  },
                },
                {
                  label: "Non",
                  hintText: "Le requérant sera invité à compléter son dossier",
                  nativeInputProps: {
                    checked: false,
                    onChange: () => console.log("Dossier complet ?", false),
                  },
                },
              ]}
            />

            <Input
              className="fr-col-12 fr-mb-3w"
              label="Explications détaillées à desination du requérant"
              hintText="Indiquez-lui ce qu'il manque à son dossier pour qu'il soit complet"
              textArea={true}
              disabled={sauvegardeEnCours}
              nativeTextAreaProps={{
                rows: 5,
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
                children: sauvegardeEnCours
                  ? "Enregistrement..."
                  : "Revoir les pièces jointes",
                priority: sauvegardeEnCours
                  ? "tertiary no outline"
                  : "secondary",
                disabled: sauvegardeEnCours,
                onClick: () => {
                  setIndex(0);
                  setEtape("pieces_jointes");
                },
              },
              {
                children: "Envoyer",
                priority: "primary",
                disabled: sauvegardeEnCours,
                onClick: () => {
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
              },
            ]}
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
