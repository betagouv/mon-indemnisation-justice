import { AfficherPieceJointe } from "@/apps/requerant/dossier/components/PieceJointe/AfficherPieceJointe";
import { PieceJointe } from "@/apps/requerant/models";
import {
  Modale,
  ModaleProps,
  ModaleRef,
} from "@/common/composants/dsfr/Modale.tsx";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import React, {
  ForwardedRef,
  forwardRef,
  useImperativeHandle,
  useRef,
} from "react";

export type SupprimerPiecesJointesModaleRef = {
  ouvrir: () => void;
  fermer: () => void;
};

export type SupprimerPiecesJointesModaleProps = Omit<
  ModaleProps,
  "children" | "id" | "title" | "titleProps" | "titleAs"
> & {
  pieceJointe: PieceJointe;
  onConfirme: (pieceJointe: PieceJointe) => Promise<void>;
};

export const SupprimerPieceJointeModale = forwardRef<
  SupprimerPiecesJointesModaleRef,
  SupprimerPiecesJointesModaleProps
>(
  (
    { pieceJointe, onConfirme, ...props }: SupprimerPiecesJointesModaleProps,
    ref: ForwardedRef<SupprimerPiecesJointesModaleRef>,
  ) => {
    const refModale = useRef<ModaleRef>(null);

    const fermer = () => {
      refModale.current?.fermer();
    };

    useImperativeHandle(ref, () => ({
      ouvrir: () => {
        refModale.current?.ouvrir();
      },
      fermer: () => {
        refModale.current?.fermer();
      },
    }));

    return (
      <Modale
        {...props}
        title="Supprimer la pièce jointe"
        id="supprimer-piece-jointe-modale"
        ref={refModale}
        size="large"
      >
        <p>
          Vous souhaitez supprimer la pièce jointe "{pieceJointe.nom}" de type{" "}
          {pieceJointe.type.libelle({ enCapitales: false, court: true })},
          confirmez-vous ?
        </p>
        <AfficherPieceJointe pieceJointe={pieceJointe} />

        <ButtonsGroup
          inlineLayoutWhen="always"
          alignment="right"
          buttonsIconPosition="right"
          buttonsSize="small"
          buttons={[
            {
              priority: "secondary",
              children: "Annuler",
              onClick: () => refModale.current?.fermer(),
            },
            {
              priority: "primary",
              children: "Confirmer",
              iconId: "fr-icon-delete-line",
              onClick: () => onConfirme(pieceJointe),
            },
          ]}
        />
      </Modale>
    );
  },
);
