import { Document, DocumentType } from "@/apps/agent/dossiers/models";
import { DossierDetail } from "@/apps/agent/dossiers/models/Dossier";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { observer } from "mobx-react-lite";
import React, { useState } from "react";
import ReactQuill from "react-quill-new";

type ValidationAcceptationAction = "verification" | "edition" | "relecture";

interface ValidationAcceptationEtat {
  action?: ValidationAcceptationAction;
  corpsCourrier: string;
  sauvegardeEnCours: boolean;
  arreteDePaiement?: Document;
}

const validationAcceptationActionModale = createModal({
  id: "modale-validation-acceptation",
  isOpenedByDefault: false,
});

const PieceJointe = function PieceJointeComponent({
  pieceJointe,
}: {
  pieceJointe: Document;
}) {
  return (
    <div className="fr-grid-row fr-my-2w">
      {pieceJointe.mime == "application/pdf" ? (
        <object
          data={pieceJointe.url}
          type="application/pdf"
          style={{
            width: "100%",
            aspectRatio: "210/297",
          }}
        ></object>
      ) : (
        <img
          src={pieceJointe.url}
          alt={pieceJointe.originalFilename}
          style={{
            width: "100%",
            maxHeight: "100vh",
            objectFit: "contain",
          }}
        />
      )}
    </div>
  );
};

export const ValidationAcceptationDossier = observer(
  function ValidationAcceptationDossierComponent({
    dossier,
  }: {
    dossier: DossierDetail;
  }) {
    const estTailleFichierOk = (fichier?: File) =>
      fichier && fichier.size < 10 * 1024 * 1024;
    const estTypeFichierOk = (fichier?: File) =>
      fichier && ["application/pdf"].includes(fichier.type);

    // Indique l'état de la validation en cours
    const [etatValidation, setEtatValidation]: [
      ValidationAcceptationEtat,
      (etat: ValidationAcceptationEtat) => void,
    ] = useState({
      sauvegardeEnCours: false,
      action: "verification",
    } as ValidationAcceptationEtat);

    // Actions
    const annuler = () => {
      setEtatValidation({
        sauvegardeEnCours: false,
        action: "verification",
      } as ValidationAcceptationEtat);
      validationAcceptationActionModale.close();
    };

    const verifierDeclarationAcceptation = () => {
      validationAcceptationActionModale.open();
      setEtatValidation({ ...etatValidation, action: "verification" });
    };
    const editer = () =>
      setEtatValidation({ ...etatValidation, action: "edition" });

    const editerCorpsCourrier = (corpsCourrier: string) => {
      console.log(corpsCourrier);
      setEtatValidation({ ...etatValidation, corpsCourrier });
    };

    const genererArretePaiement = () => {
      // Envoi à l'API pour impression, récupération du document
      console.log(etatValidation.corpsCourrier);
      relire();
    };

    const relire = () =>
      setEtatValidation({ ...etatValidation, action: "relecture" });

    return (
      <>
        <ButtonsGroup
          inlineLayoutWhen="always"
          alignment="right"
          buttonsSize="small"
          buttons={[
            {
              children:
                "Vérifier la déclaration d'acceptation et générer l'arrêté de paiement",
              onClick: () => verifierDeclarationAcceptation(),
            } as ButtonProps,
          ]}
        />

        <validationAcceptationActionModale.Component
          title="Vérifier la déclaration d'acceptation"
          size="large"
          iconId="fr-icon-search-line"
        >
          {etatValidation.action === "verification" && (
            <>
              <p>
                Inspectez le document de déclaration d'acceptation ci-dessous et
                vérifiez que tous les champs sont correctement remplis :
              </p>
              <PieceJointe
                pieceJointe={dossier
                  .getDocumentsType(DocumentType.TYPE_COURRIER_REQUERANT)
                  .at(0)}
              />
              <ButtonsGroup
                inlineLayoutWhen="always"
                alignment="right"
                buttonsSize="small"
                buttons={[
                  {
                    children: "Valider et éditer l'arrêté de paiement",
                    onClick: () => editer(),
                  } as ButtonProps,
                ]}
              />
            </>
          )}
          {etatValidation.action === "edition" && (
            <>
              <div className="fr-input-group fr-my-2w">
                <ReactQuill
                  theme="snow"
                  value={etatValidation.corpsCourrier}
                  onChange={(value) => editerCorpsCourrier(value)}
                  readOnly={etatValidation.sauvegardeEnCours}
                />
                <div />
              </div>

              <ButtonsGroup
                inlineLayoutWhen="always"
                alignment="right"
                buttonsSize="small"
                buttons={[
                  {
                    children: "Annuler",
                    priority: "tertiary no outline",
                    onClick: () => annuler(),
                  } as ButtonProps,
                  /*
                  {
                    children: "Re-vérifier la déclaration",
                    priority: "secondary",
                    onClick: () => verifierDeclarationAcceptation(),
                  } as ButtonProps,
                  */
                  {
                    children: "Générer le document",
                    iconId: "fr-icon-printer-line",
                    disabled:
                      etatValidation.sauvegardeEnCours ||
                      !etatValidation.corpsCourrier,
                    onClick: () => relire(),
                  } as ButtonProps,
                  {
                    children: "Visualiser",
                    iconId: "fr-icon-search-line",
                    disabled:
                      etatValidation.sauvegardeEnCours ||
                      !etatValidation.arreteDePaiement,
                    onClick: () => relire(),
                  } as ButtonProps,
                ]}
              />
            </>
          )}
          {etatValidation.action === "relecture" && (
            <>
              <PieceJointe pieceJointe={etatValidation.arreteDePaiement} />
              <ButtonsGroup
                inlineLayoutWhen="always"
                alignment="right"
                buttonsSize="small"
                buttons={[
                  {
                    children: "Valider",
                    onClick: () => editer(),
                  } as ButtonProps,
                ]}
              />
            </>
          )}
        </validationAcceptationActionModale.Component>
      </>
    );
  },
);
