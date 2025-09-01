import React, { useCallback, useState } from "react";
import { Agent, Document, DocumentType, DossierDetail } from "@/common/models";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { plainToInstance } from "class-transformer";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Upload } from "@codegouvfr/react-dsfr/Upload";
import { Alert } from "@codegouvfr/react-dsfr/Alert";

const _modale = createModal({
  id: "modale-supprimer-piece-jointe",
  isOpenedByDefault: false,
});

const ouvrirModaleSuppressionPieceJointe = _modale.open;

const component = function SuppressionPieceJointe({
  pieceJointe,
  dossier,
  onSupprime = null,
}: {
  pieceJointe: Document;
  dossier: DossierDetail;
  onSupprime?: () => void;
}) {
  // Indique si la sauvegarde des notes de suivi est en cours
  const [sauvegardeEnCours, setSauvegarderEnCours]: [
    boolean,
    (mode: boolean) => void,
  ] = useState(false);

  const supprimerPieceJointe = useCallback(async () => {
    setSauvegarderEnCours(true);

    const response = await fetch(
      `/agent/document/${pieceJointe.id}/supprimer`,
      {
        method: "DELETE",
        headers: {
          Accept: "application/json",
        },
      },
    );

    if (response.ok) {
      dossier.removeDocument(pieceJointe);
      _modale.close();
      onSupprime?.();
    }

    setSauvegarderEnCours(false);
  }, [pieceJointe.id]);

  return (
    <_modale.Component
      title={` Supprimer la pièce jointe "${pieceJointe.originalFilename}"`}
      iconId="fr-icon-delete-bin-line"
      size="large"
    >
      <Alert
        severity="warning"
        title="La suppression est définitive"
        description={
          <>
            <p>
              Vous vous apprêtez à supprimer le document "
              {pieceJointe.originalFilename}" de type {pieceJointe.type.libelle}
              .
            </p>
            <p>
              Cette action étant définitive, assurez-vous d'être certain de
              vouloir continuer.
            </p>
          </>
        }
      />

      <ButtonsGroup
        className="fr-my-2w"
        inlineLayoutWhen="always"
        alignment="right"
        buttonsIconPosition="right"
        buttons={[
          {
            priority: "tertiary no outline",
            disabled: sauvegardeEnCours,
            children: "Annuler",
            onClick: () => _modale.close(),
          },
          {
            priority: "primary",
            disabled: sauvegardeEnCours,
            onClick: () => supprimerPieceJointe(),
            iconId: "fr-icon-delete-bin-line",
            children: "Supprimer",
          },
        ]}
      />
    </_modale.Component>
  );
};

export {
  component as SuppressionPieceJointe,
  ouvrirModaleSuppressionPieceJointe,
};
