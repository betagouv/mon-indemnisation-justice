import {
  Document,
  DocumentType,
  EtatDossier,
} from "@/apps/agent/dossiers/models";
import { DossierDetail } from "@/apps/agent/dossiers/models/Dossier";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { plainToInstance } from "class-transformer";
import { observer } from "mobx-react-lite";
import React, { useState } from "react";
import ReactQuill, { Quill } from "react-quill-new";

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

// Patch pour la gestion des ul/ol dans Quill
import List, { ListContainer } from "quill/formats/list";

class UListContainer extends ListContainer {}
UListContainer.blotName = "ulist-container";
UListContainer.tagName = "UL";

class UListItem extends List {
  static register() {
    Quill.register(UListContainer);
  }
}

UListItem.blotName = "ulist";
UListItem.tagName = "LI";

UListContainer.allowedChildren = [UListItem];
UListItem.requiredContainer = UListContainer;

Quill.register({
  "formats/list": List,
  "formats/ulist": UListItem,
});

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
      corpsCourrier: dossier.corpsCourrier,
      sauvegardeEnCours: false,
      action: "verification",
      arreteDePaiement:
        dossier.getDocumentsType(DocumentType.TYPE_ARRETE_PAIEMENT).at(0) ??
        null,
    } as ValidationAcceptationEtat);

    // Actions
    const annuler = () => {
      setEtatValidation({
        corpsCourrier: dossier.corpsCourrier,
        sauvegardeEnCours: false,
        action: "verification",
      } as ValidationAcceptationEtat);
      validationAcceptationActionModale.close();
    };

    const setSauvegardeEnCours = (sauvegardeEnCours: boolean = true) =>
      setEtatValidation({ ...etatValidation, sauvegardeEnCours });

    const verifierDeclarationAcceptation = () => {
      validationAcceptationActionModale.open();
      setEtatValidation({ ...etatValidation, action: "verification" });
    };
    const editer = () =>
      setEtatValidation({ ...etatValidation, action: "edition" });

    const editerCorpsCourrier = (corpsCourrier: string) => {
      setEtatValidation({ ...etatValidation, corpsCourrier });
    };

    const genererArretePaiement = async () => {
      // Envoi à l'API pour impression, récupération du document
      setSauvegardeEnCours(true);

      const response = await fetch(
        `/agent/redacteur/dossier/${dossier.id}/arrete-paiement/generer.json`,
        {
          method: "POST",
          headers: {
            "Content-type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify({
            corpsCourrier: etatValidation.corpsCourrier,
          }),
        },
      );
      let document: Document;

      if (response.ok) {
        const data = await response.json();
        document = plainToInstance(Document, data.document);
        setEtatValidation({
          ...etatValidation,
          arreteDePaiement: document,
        });
      }

      setEtatValidation({
        ...etatValidation,
        action: "relecture",
        arreteDePaiement: document,
      });
    };

    const relire = () => {
      setEtatValidation({ ...etatValidation, action: "relecture" });
    };

    const valider = async () => {
      // Appel à l'API pour valider le document
      const response = await fetch(
        `/agent/redacteur/dossier/${dossier.id}/arrete-paiement/valider.json`,
        {
          method: "POST",
          headers: {
            "Content-type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify({
            document: etatValidation.arreteDePaiement.id,
          }),
        },
      );

      if (response.ok) {
        const data = await response.json();
        dossier.changerEtat(plainToInstance(EtatDossier, data.etat));
        dossier.addDocument(etatValidation.arreteDePaiement);
      }

      setSauvegardeEnCours(false);
      validationAcceptationActionModale.close();
    };

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
                  {
                    children: "Générer le document",
                    iconId: "fr-icon-printer-line",
                    priority: "secondary",
                    disabled:
                      etatValidation.sauvegardeEnCours ||
                      !etatValidation.corpsCourrier,
                    onClick: () => genererArretePaiement(),
                  } as ButtonProps,
                  {
                    children: "Visualiser",
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
                    children: "Annuler",
                    priority: "tertiary no outline",
                    onClick: () => annuler(),
                  } as ButtonProps,
                  {
                    iconId: "fr-icon-pencil-line",
                    children: "Éditer",
                    priority: "secondary",
                    onClick: () => editer(),
                  } as ButtonProps,
                  {
                    iconId: "fr-icon-check-line",
                    children: "Valider",
                    onClick: () => valider(),
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
