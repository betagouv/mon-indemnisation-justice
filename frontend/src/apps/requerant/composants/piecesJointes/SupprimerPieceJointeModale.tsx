import { AfficherPieceJointe } from "@/apps/requerant/dossier/components/PieceJointe/AfficherPieceJointe";
import { PieceJointe } from "@/apps/requerant/models";
import {
  ModaleDsfr,
  useModaleDsfr,
} from "@/common/composants/dsfr/useModaleDsfr.ts";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import React, { useMemo, useState } from "react";

export type SupprimerPieceJointeModaleProps = {
  pieceJointe: PieceJointe;
  onConfirme: (pieceJointe: PieceJointe) => Promise<void>;
};

/**
 * Crée le composant de modale de suppression de pièce jointe, à partir d'une `modale`
 * donnée.
 *
 * Chaque appel retournant un nouveau composant, l'appel doit donc être mémoïsé.
 */
const creerSupprimerPieceJointeModale = (modale: ModaleDsfr) =>
  function SupprimerPieceJointeModale({
    pieceJointe,
    onConfirme,
  }: SupprimerPieceJointeModaleProps) {
    const [suppressionEnCours, setSuppressionEnCours] = useState(false);

    const confirmer = async () => {
      setSuppressionEnCours(true);
      try {
        await onConfirme(pieceJointe);
        modale.close();
      } finally {
        setSuppressionEnCours(false);
      }
    };

    return (
      <modale.Component
        title="Supprimer la pièce jointe"
        size="large"
        concealingBackdrop={!suppressionEnCours}
      >
        <p>
          Vous souhaitez supprimer la pièce jointe "{pieceJointe.nom}" de type{" "}
          {pieceJointe.type.libelle({ enCapitales: false, court: true })},
          confirmez-vous ?
        </p>
        <AfficherPieceJointe pieceJointe={pieceJointe} telecharger={false} />

        <ButtonsGroup
          inlineLayoutWhen="always"
          alignment="right"
          buttonsIconPosition="right"
          buttonsSize="small"
          buttons={[
            {
              priority: "secondary",
              children: "Annuler",
              disabled: suppressionEnCours,
              onClick: () => modale.close(),
            },
            {
              priority: "primary",
              children: "Confirmer",
              iconId: "fr-icon-delete-line",
              disabled: suppressionEnCours,
              onClick: confirmer,
            },
          ]}
        />
      </modale.Component>
    );
  };

/**
 * Fournit une modale de confirmation de suppression de pièce jointe, propre au
 * composant appelant, ainsi que les fonctions pour l'ouvrir et la fermer.
 */
export const useSupprimerPieceJointeModale = () => {
  const modale = useModaleDsfr("supprimer-piece-jointe-modale");

  const SupprimerPieceJointeModale = useMemo(
    () => creerSupprimerPieceJointeModale(modale),
    [modale],
  );

  return {
    modale: SupprimerPieceJointeModale,
    ouvrir: modale.open,
    fermer: modale.close,
  };
};
