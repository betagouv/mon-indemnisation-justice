import React, { useState, useCallback, useEffect } from "react";
import { observer } from "mobx-react-lite";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Document, DossierDetail } from "@/apps/agent/dossiers/models";
import { QuillEditor } from "@/apps/agent/dossiers/components/consultation/editor";
import { PieceJointe } from "@/apps/agent/dossiers/components/consultation/piecejointe";
import { plainToInstance } from "class-transformer";
import { DeltaStatic, EmitterSource } from "react-quill-new";

export type EditeurMode = "edition" | "visualisation";

export const EditeurDocument = observer(function EditeurDocumentComponent({
  document,
  className = null,
  lectureSeule = false,
  // Mode "controlled" : permet au composant parent de prendre le contrôle sur le mode en cours (édition ou visualisation)
  mode = null,
  // Callback appelé lorsque le corps du document a été édité par l'utilisateur
  onEdite = null,
  // Callback appelé lorsque le document sors de l'impression (i.e. génération d'un nouveau fichier PDF avec le corps mis à jour)
  onImprime = null,
  // Callback appelé avant / après l'impression
  onImpression = null,
}: {
  document?: Document;
  className?: string;
  lectureSeule?: boolean;
  mode?: EditeurMode;
  onEdite?: (corps: string) => void;
  onImprime?: (document: Document) => void;
  onImpression?: (impressionEnCours: boolean) => void;
}) {
  const [modeEdition, setModeEdition] = useState<boolean>(true);
  const [impressionEnCours, setImpressionEnCours] = useState<boolean>(false);
  const [modificationsEnAttente, setModificationsEnAttente] =
    useState<boolean>(false);
  const [corps, setCorps] = useState<string>(document.corps);

  const modifier = (corps: string) => {
    setModificationsEnAttente(true);
    setCorps(corps);
    onEdite?.(corps);
  };

  const imprimer = useCallback(async () => {
    setImpressionEnCours(true);
    onImpression?.(impressionEnCours);
    const response = await fetch(
      `/api/agent/document/${document.id}/imprimer`,
      {
        headers: {
          "Content-type": "application/json",
        },
        method: "PUT",
        body: JSON.stringify({ corps }),
      },
    );

    if (response.ok) {
      const data = await response.json();

      const document = plainToInstance(Document, data);
      onImprime?.(document);
    }

    setImpressionEnCours(false);
    setModificationsEnAttente(false);
    onImpression?.(impressionEnCours);
  }, [document.id, corps]);

  // Mode "controlled" : lorsque l'on bascule en mode visualisation et que le
  // document a été édité, alors on automatise l'impression
  useEffect(() => {
    if (mode === "visualisation" && modificationsEnAttente) {
      imprimer();
    }
  }, [mode]);

  const visualiser = async () => {
    if (modificationsEnAttente) {
      await imprimer();
    }
    setModeEdition(false);
  };

  return (
    <div className={className}>
      {null === mode && (
        <div className="fr-col-12 fr-mb-2w">
          <ButtonsGroup
            inlineLayoutWhen="always"
            alignment="right"
            buttonsIconPosition="right"
            buttons={[
              modeEdition
                ? {
                    children: "Visualiser",
                    priority: "secondary",
                    iconId:
                      corps !== document.corps
                        ? "fr-icon-printer-line"
                        : "fr-icon-eye-line",
                    disabled: impressionEnCours,
                    onClick: async () => visualiser(),
                  }
                : {
                    children: "Éditer",
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
      )}
      <div className="fr-col-12">
        {(null !== mode ? mode === "edition" : modeEdition === true) && (
          <QuillEditor
            value={corps}
            onChange={(value) => modifier(value)}
            readOnly={impressionEnCours || lectureSeule}
          />
        )}

        {mode === "visualisation" && (
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
});
