import React, { useState, useCallback, useEffect } from "react";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Document } from "@/common/models";
import { QuillEditor } from "@/apps/agent/dossiers/components/consultation/editor";
import { PieceJointe } from "@/apps/agent/dossiers/components/consultation/piecejointe";
import { DocumentManagerImpl } from "@/common/services/agent";
import { useInjection } from "inversify-react";
import { DocumentManagerInterface } from "@/common/services/agent/document.ts";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { Loader } from "@/common/components/Loader.tsx";

export type EditeurMode = "edition" | "visualisation";

export const EditeurDocument = function EditeurDocumentComponent({
  document,
  // Callback générant un document
  //regenererDocument,
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
  //regenererDocument: () => Document | Promise<Document>
  className?: string;
  lectureSeule?: boolean;
  onEdite?: (corps: string) => void;
  onImprime?: (document: Document) => void;
  onImpression?: (impressionEnCours: boolean) => void;
}) {
  const documentManager: DocumentManagerInterface =
    useInjection<DocumentManagerInterface>(DocumentManagerImpl);

  const [modeEdition, setModeEdition] = useState<boolean>(true);
  const [impressionEnCours, setImpressionEnCours] = useState<boolean>(false);
  const [modificationsEnAttente, setModificationsEnAttente] =
    useState<boolean>(false);
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
  // document est édité depuis 3s
  useEffect(() => {
    if (modificationsEnAttente && !impressionEnCours) {
      if (modeEdition) {
        const impressionProgrammee = setTimeout(() => imprimer(), 3000);

        return () => clearTimeout(impressionProgrammee);
      } else {
        imprimer();
      }
    }
  }, [modeEdition, modificationsEnAttente, impressionEnCours]);

  useEffect(() => {
    setCorps(document.corps ?? null);
  }, [document.id]);

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
                <span aria-hidden="true" className="fr-icon-check-line"></span>
              </>
            )}
          </span>

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
                readOnly={impressionEnCours || lectureSeule}
              />
            ) : (
              <>
                <p>Ce document a été édité en dehors de la plateforme</p>

                <p>
                  Si vous voulez l'éditer vous pouvez le re-générer mais le PDF
                  actuel <b>sera perdu</b>
                </p>

                <Button
                  title="Re-générer"
                  iconId="fr-icon-restart-line"
                  linkProps={{}}
                />
              </>
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
