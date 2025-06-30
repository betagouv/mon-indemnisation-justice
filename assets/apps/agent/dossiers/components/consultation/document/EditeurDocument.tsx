import React, { useState, useCallback, useEffect } from "react";
import { observer } from "mobx-react-lite";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Document, DossierDetail } from "@/apps/agent/dossiers/models";
import { QuillEditor } from "@/apps/agent/dossiers/components/consultation/editor";
import { PieceJointe } from "@/apps/agent/dossiers/components/consultation/piecejointe";
import { plainToInstance } from "class-transformer";
import { DeltaStatic, EmitterSource } from "react-quill-new";

export const EditeurDocument = observer(function EditeurDocumentComponent({
  dossier,
  document,
  className = null,
  lectureSeule = false,
  onChange = null,
  onEdite = null,
  onImpression = null,
}: {
  dossier: DossierDetail;
  document?: Document;
  className?: string;
  lectureSeule?: boolean;
  onChange?: (document: Document) => void;
  onEdite?: (corps: string) => void;
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
    const response = await fetch(`/agent/document/${document.id}/imprimer`, {
      method: "PUT",
      body: JSON.stringify({ corps }),
    });

    if (response.ok) {
      const data = await response.json();

      const document = plainToInstance(Document, data);
      onChange?.(document);
    }

    setImpressionEnCours(false);
    setModificationsEnAttente(false);
    onImpression?.(impressionEnCours);
  }, [document.id, corps]);

  const visualiser = async () => {
    console.log(modificationsEnAttente);
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
      <div className="fr-col-12">
        {modeEdition ? (
          <QuillEditor
            value={corps}
            onChange={(value) => modifier(value)}
            readOnly={impressionEnCours || lectureSeule}
          />
        ) : (
          <PieceJointe pieceJointe={document} />
        )}
      </div>
    </div>
  );
});
