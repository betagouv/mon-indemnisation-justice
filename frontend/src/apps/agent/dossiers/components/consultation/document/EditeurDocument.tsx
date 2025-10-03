import React, { useState, useCallback, useEffect } from "react";
import { observer } from "mobx-react-lite";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Document } from "@/common/models";
import { QuillEditor } from "@/apps/agent/dossiers/components/consultation/editor";
import { PieceJointe } from "@/apps/agent/dossiers/components/consultation/piecejointe";
import { DocumentManagerImpl } from "@/common/services/agent";
import { useInjection } from "inversify-react";
import { DocumentManagerInterface } from "@/common/services/agent/document.ts";

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
      onImpression?.(impressionEnCours);
      setImpressionEnCours(true);
      onImpression?.(impressionEnCours);
      document = await documentManager.imprimer(document, corps);
      onImprime?.(document);

      setImpressionEnCours(false);
      setModificationsEnAttente(false);
    }
  }, [document.id, corps]);

  // Lancer l'impression lorsque l'on bascule en mode visualisation et que le document a été édité
  useEffect(() => {
    if (!modeEdition && modificationsEnAttente) {
      imprimer();
    }
  }, [modeEdition]);

  const visualiser = async () => {
    if (modificationsEnAttente) {
      await imprimer();
    }
    setModeEdition(false);
  };

  return (
    <div className={className}>
      <div className="fr-col-12 fr-mb-2w">
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
              </>
            )}
          </>
        ) : (
          <>
            {impressionEnCours ? (
              <p>Impression en cours...</p>
            ) : (
              <PieceJointe pieceJointe={document} />
            )}
          </>
        )}
      </div>
    </div>
  );
};
