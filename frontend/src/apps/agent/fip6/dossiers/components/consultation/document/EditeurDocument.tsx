import React, { useState, useCallback, useEffect } from "react";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Document } from "@/common/models";
import { QuillEditor } from "@/apps/agent/fip6/dossiers/components/consultation/editor";
import { PieceJointe } from "@/apps/agent/fip6/dossiers/components/consultation/piecejointe";
import { useInjection } from "inversify-react";
import { DocumentManagerInterface } from "@/common/services/agent/document.ts";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { Loader } from "@/common/components/Loader.tsx";
import { Alert } from "@codegouvfr/react-dsfr/Alert";

export type EditeurMode = "edition" | "visualisation";

type EditeurPosition = {
  // En millisecondes
  timestamp?: number;
  positionCurseur?: number;
  longueurSelection?: number;
};

export const EditeurDocument = function EditeurDocumentComponent({
  document,
  // Callback générant un document
  regenererDocument,
  className,
  lectureSeule = false,
  // Callback appelé lorsque le corps du document a été édité par l'utilisateur
  onEdite,
  // Callback appelé lorsque le document sors de l'impression (i.e. génération d'un nouveau fichier PDF avec le corps mis à jour)
  onImprime,
  // Callback appelé avant / après l'impression
  onImpression,
}: {
  document: Document;
  regenererDocument?: () => void | Promise<void>;
  className?: string;
  lectureSeule?: boolean;
  onEdite?: (corps: string) => void;
  onImprime?: (document: Document) => void;
  onImpression?: (impressionEnCours: boolean) => void;
}) {
  const documentManager: DocumentManagerInterface =
    useInjection<DocumentManagerInterface>(DocumentManagerInterface.$);

  const [modeEdition, setModeEdition] = useState<boolean>(!!document.corps);
  const [impressionEnCours, setImpressionEnCours] = useState<boolean>(false);
  const [modificationsEnAttente, setModificationsEnAttente] =
    useState<boolean>(false);
  const [positionEditeur, setPositionEditeur] = useState<EditeurPosition>({});
  const [corps, setCorps] = useState<string | null>(document.corps ?? null);

  const modifier = (corps: string) => {
    setModificationsEnAttente(true);
    setCorps(corps);
    onEdite?.(corps);
  };

  const imprimer = useCallback(async () => {
    if (corps) {
      onImpression?.(true);
      setImpressionEnCours(true);
      document = await documentManager.imprimer(document, corps);
      onImprime?.(document);

      setImpressionEnCours(false);
      onImpression?.(false);
      setModificationsEnAttente(false);
    }
  }, [document.id, corps]);

  // Lancer l'impression lorsque l'on bascule en mode visualisation et que le document a été édité ou que le corps du
  // document est édité depuis 5s
  useEffect(() => {
    if (modificationsEnAttente && !impressionEnCours) {
      if (modeEdition) {
        const impressionProgrammee = setTimeout(() => {
          if (
            !positionEditeur.timestamp ||
            new Date().getTime() - positionEditeur.timestamp >= 5000
          ) {
            imprimer();
          }
        }, 5000);

        return () => clearTimeout(impressionProgrammee);
      } else {
        imprimer();
      }
    }
  }, [modeEdition, modificationsEnAttente, impressionEnCours, positionEditeur]);

  useEffect(() => {
    setCorps(document.corps ?? null);
    setImpressionEnCours(false);
  }, [document.corps]);

  const visualiser = async () => {
    if (modificationsEnAttente) {
      await imprimer();
    }
    setModeEdition(false);
  };

  return (
    <div className={className}>
      <div className="fr-col-12 fr-mb-2w">
        <div className="fr-grid-row fr-grid-row--right" style={{ gap: "1rem" }}>
          {corps && (
            <span className="fr-text--sm fr-mx-0 fr-my-auto">
              {impressionEnCours ? (
                <>
                  <i>Regénération du PDF en cours </i>
                  <span aria-hidden="true" className="fr-icon-more-line"></span>
                </>
              ) : modificationsEnAttente ? (
                <>
                  Modifications en attente{" "}
                  <span
                    className="fr-icon-refresh-line"
                    aria-hidden="true"
                  ></span>
                </>
              ) : (
                <>
                  Document PDF à jour{" "}
                  <span
                    aria-hidden="true"
                    className="fr-icon-check-line"
                  ></span>
                </>
              )}
            </span>
          )}

          <ButtonsGroup
            inlineLayoutWhen="always"
            alignment="right"
            buttonsSize="small"
            buttonsIconPosition="right"
            buttons={[
              modeEdition
                ? {
                    children: "Visualiser le PDF",
                    priority: "secondary",
                    iconId:
                      corps !== document?.corps
                        ? "fr-icon-printer-line"
                        : "fr-icon-eye-line",
                    disabled: impressionEnCours,
                    onClick: async () => visualiser(),
                  }
                : {
                    children: "Éditer le corps",
                    priority: "secondary",
                    disabled: impressionEnCours,
                    iconId: "fr-icon-pencil-line",
                    onClick: () => {
                      setModeEdition(true);
                    },
                  },
            ]}
          />
        </div>
      </div>
      <div className="fr-col-12">
        {modeEdition ? (
          <>
            {corps ? (
              <QuillEditor
                value={corps}
                onChange={(value) => modifier(value)}
                onMove={(cursorIndex, selectionLength) => {
                  if (
                    positionEditeur.positionCurseur != cursorIndex ||
                    positionEditeur.longueurSelection != selectionLength
                  ) {
                    setPositionEditeur({
                      timestamp: new Date().getTime() / 1000,
                      positionCurseur: cursorIndex,
                      longueurSelection: selectionLength,
                    });
                  }
                }}
                readOnly={impressionEnCours || lectureSeule}
              />
            ) : (
              <div className="fr-grid-row fr-grid-row--center fr-mb-10w">
                <p className="fr-my-3v">
                  Ce document semble avoir été édité en dehors de la plateforme.
                </p>

                {regenererDocument ? (
                  <>
                    <Alert
                      title={false}
                      className="fr-my-3v"
                      severity="warning"
                      small={false}
                      description={
                        <>
                          Si vous voulez l'éditer vous pouvez le re-générer mais
                          le PDF actuel <b>sera perdu</b>.
                        </>
                      }
                    />

                    <Button
                      className="fr-my-3v"
                      title="Re-générer et éditer le document"
                      children="Re-générer et éditer le document"
                      priority="primary"
                      size="small"
                      disabled={impressionEnCours}
                      iconId="fr-icon-refresh-line"
                      onClick={() => {
                        setImpressionEnCours(true);
                        regenererDocument();
                      }}
                    />
                  </>
                ) : (
                  <></>
                )}
              </div>
            )}
          </>
        ) : (
          <>
            {impressionEnCours ? (
              <Loader />
            ) : (
              <PieceJointe pieceJointe={document} />
            )}
          </>
        )}
      </div>
    </div>
  );
};
