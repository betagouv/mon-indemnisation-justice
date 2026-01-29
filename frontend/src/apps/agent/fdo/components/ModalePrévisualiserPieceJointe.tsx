import { createModal } from "@codegouvfr/react-dsfr/Modal";
import React, { useCallback, useState } from "react";
import { Document, DocumentType } from "@/common/models";
import { DeclarationFDOBrisPorte } from "@/apps/agent/fdo/models/DeclarationFDOBrisPorte.ts";
import {
  PieceJointe,
  TelechargerPieceJointe,
} from "@/apps/agent/fip6/dossiers/components/consultation/piecejointe";
import { Loader } from "@/common/components/Loader.tsx";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";

const _modale = createModal({
  id: "modale-previsualiser-piece-jointe-declaration-bris-porte",
  isOpenedByDefault: false,
});

export interface ModalePrevisualiserPieceJointeRef {
  previsualiserPieceJointe: (pieceJointe: Document) => void;
  supprimerPieceJointe: (pieceJointe: Document) => void;
}

export interface ModalePrevisualiserPieceJointeProps {
  declarationFDO: DeclarationFDOBrisPorte;
  onSupprime: (pieceJointe: Document) => void | Promise<void>;
}

export const ModalePrevisualiserPieceJointe = React.forwardRef<
  ModalePrevisualiserPieceJointeRef,
  ModalePrevisualiserPieceJointeProps
>(
  (
    { declarationFDO, onSupprime },
    ref: React.Ref<ModalePrevisualiserPieceJointeRef>,
  ) => {
    const [pieceJointe, selectionnerPieceJointe] = useState<
      Document | undefined
    >(undefined);

    // Exposer les fonctions au composant appelant
    React.useImperativeHandle(ref, () => ({
      previsualiserPieceJointe: (pieceJointe: Document) => {
        setAvertissementEnCours(false);
        selectionnerPieceJointe(pieceJointe);
        _modale.open();
      },
      supprimerPieceJointe(pieceJointe: Document): void {},
    }));
    // Indique si l'avertissement que la suppression est définitive est affiché
    const [avertissementEnCours, setAvertissementEnCours] = useState(false);

    // Indique si la suppression est en cours
    const [suppressionEnCours, setSuppressionEnCours] =
      useState<boolean>(false);

    const fermer = () => {
      _modale.close();
      setAvertissementEnCours(false);
      setSuppressionEnCours(false);
    };

    const supprimerPieceJointe = useCallback(
      async ({
        declarationFDO,
        pieceJointe,
      }: {
        declarationFDO: DeclarationFDOBrisPorte;
        pieceJointe: Document;
      }) => {
        setSuppressionEnCours(true);

        const response = await fetch(
          `/api/agent/fdo/bris-de-porte/${declarationFDO.id}/piece-jointe/${pieceJointe.id}/${pieceJointe.fileHash}/supprimer`,
          {
            method: "DELETE",
          },
        );

        if (response.ok) {
          await onSupprime(pieceJointe);
        } else {
          // TODO : afficher un message d'erreur
        }

        setSuppressionEnCours(false);
        _modale.close();
      },
      [declarationFDO],
    );

    return (
      <_modale.Component
        size="large"
        title={`Aperçu de la pièce jointe "${pieceJointe?.filename}"`}
        concealingBackdrop={!suppressionEnCours}
      >
        <div className="fr-grid-row">
          <div className="fr-col-12">
            {pieceJointe ? (
              avertissementEnCours ? (
                <>
                  <Alert
                    severity="warning"
                    title="Voulez-vous suprimer cette pièce jointe ?"
                    description={
                      <p>
                        Une fois supprimée, elle n'apparaîtra pas dans la
                        déclaration.
                      </p>
                    }
                  />
                </>
              ) : (
                <>
                  <TelechargerPieceJointe
                    pieceJointe={pieceJointe}
                    className="fr-my-1w"
                  />
                  <PieceJointe
                    className="fr-my-1w"
                    pieceJointe={pieceJointe}
                    lienTelechargement={`/agent/fdo/document/${pieceJointe.id}/${pieceJointe.fileHash}`}
                  />
                </>
              )
            ) : (
              <Loader />
            )}
          </div>

          <div className="fr-col-12">
            <ButtonsGroup
              className="fr-my-1w"
              inlineLayoutWhen="always"
              alignment="right"
              buttonsIconPosition="right"
              buttonsSize="small"
              buttons={[
                {
                  children: "Fermer",
                  priority: "tertiary no outline",
                  onClick: () => fermer(),
                },
                ...(avertissementEnCours
                  ? [
                      {
                        children: "Annuler",
                        priority: "secondary",
                        onClick: () => setAvertissementEnCours(false),
                      } as ButtonProps,
                    ]
                  : []),
                {
                  children: "Supprimer",
                  iconId: "fr-icon-delete-line",
                  priority: avertissementEnCours ? "primary" : "secondary",
                  onClick: async () => {
                    if (avertissementEnCours) {
                      await supprimerPieceJointe({
                        declarationFDO,
                        pieceJointe: pieceJointe as Document,
                      });
                    } else {
                      setAvertissementEnCours(true);
                    }
                  },
                },
              ]}
            />
          </div>
        </div>
      </_modale.Component>
    );
  },
);
