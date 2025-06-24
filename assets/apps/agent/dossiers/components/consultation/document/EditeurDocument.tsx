import React, { useCallback } from "react";
import { observer } from "mobx-react-lite";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Document } from "@/apps/agent/dossiers/models";
import { QuillEditor } from "@/apps/agent/dossiers/components/consultation/editor";
import { PieceJointe } from "@/apps/agent/dossiers/components/consultation/piecejointe";

export const EditeurDocument = observer(function EditeurDocumentComponent({
  document,
  onChange,
}: {
  document?: Document;
  onChange: (document: Document) => void;
}) {
  const [modeEdition, setModeEdition] = React.useState<boolean>(true);
  const [corps, setCorps] = React.useState<string>(document.corps);

  const imprimer = useCallback(async () => {
    fetch(``);
  }, [document.id]);

  const visualiser = () => {
    if (corps !== document.corps) {
      imprimer();
    } else {
      setModeEdition(false);
    }
  };

  return (
    <>
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
                  iconId: "fr-icon-printer-line",
                  onClick: () => {
                    visualiser();
                  },
                }
              : {
                  children: "Éditer",
                  priority: "secondary",
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
          <QuillEditor value={corps} onChange={setCorps} />
        ) : (
          <PieceJointe pieceJointe={document} />
        )}
      </div>
    </>
  );
});
