import { PieceJointe } from "@/apps/requerant/models";
import classes from "@/apps/requerant/style/composants/piecesJointes/PieceJointePanelNavigation.module.css";
import { Button } from "@codegouvfr/react-dsfr/Button";
import Download from "@codegouvfr/react-dsfr/Download";
import { default as React } from "react";

export const PieceJointePanelNavigation = ({
  pieceJointe,
  nomPieceJointePrecedente,
  onSelectionPieceJointePrecedente,
  nomPieceJointeSuivante,
  onSelectionPieceJointeSuivante,
}: {
  pieceJointe: PieceJointe;
  nomPieceJointePrecedente: string;
  onSelectionPieceJointePrecedente: () => void;
  nomPieceJointeSuivante: string;
  onSelectionPieceJointeSuivante: () => void;
}) => (
  <div
    className={`${classes.pieceJointePanelNavigation} fr-grid-row fr-col-12 fr-mb-3w`}
  >
    <div
      className="fr-col-6 fr-col-md-3 fr-mb-2w"
      style={{
        display: "flex",
        flexDirection: "column",
        justifyContent: "space-around",
        alignContent: "flex-start",
        alignItems: "start",
      }}
    >
      <Button
        title="Pièce jointe précédente"
        priority="tertiary no outline"
        size="small"
        iconId="fr-icon-arrow-left-line"
        nativeButtonProps={{
          onClick: () => onSelectionPieceJointePrecedente(),
        }}
      >
        Document précédent
      </Button>
      <span className="fr-text--xs fr-text-default--grey fr-text--light fr-m-0">
        {nomPieceJointePrecedente}
      </span>
    </div>

    <div className="fr-col-12 fr-col-md-6">
      <Download
        details={`${pieceJointe.infoFichier}`}
        label={pieceJointe.nom}
        linkProps={{
          href: `${pieceJointe.url}?download`,
        }}
      />
    </div>

    <div
      className="fr-col-6 fr-col-md-3 fr-mb-2w"
      style={{
        display: "flex",
        flexDirection: "column",
        justifyContent: "space-around",
        alignContent: "flex-end",
        alignItems: "end",
      }}
    >
      <Button
        title="Pièce jointe suivante"
        priority="tertiary no outline"
        size="small"
        iconId="fr-icon-arrow-right-line"
        iconPosition="right"
        nativeButtonProps={{
          onClick: () => onSelectionPieceJointeSuivante(),
        }}
      >
        Document suivant
      </Button>
      <span className="fr-text--xs fr-text-default--grey fr-text--light fr-m-0">
        {nomPieceJointeSuivante}
      </span>
    </div>
  </div>
);
