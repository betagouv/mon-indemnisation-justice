import React, { useCallback, useRef, useState } from "react";
import {
  Agent,
  Document,
  DocumentType,
  DossierDetail,
} from "@/apps/agent/dossiers/models";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { plainToInstance } from "class-transformer";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Upload } from "@codegouvfr/react-dsfr/Upload";
import { Select } from "@codegouvfr/react-dsfr/Select";

const _modale = createModal({
  id: "modale-ajouter-piece-jointe",
  isOpenedByDefault: false,
});

type EtatAjout = "choix_type" | "televersement";

const component = function AjoutPieceJointe({
  dossier,
  agent,
  onAjoute = null,
}: {
  dossier: DossierDetail;
  agent: Agent;
  onAjoute?: (nouvellePieceJointe: Document) => void;
}) {
  // Ref sur le champ de sélection du fichier, pour pouvoir le réinitialiser
  const refChampFichier = useRef<HTMLInputElement>();

  // Type de document associé à la pièce jointe téléversée
  const [typePJ, setTypePj]: [DocumentType, (typePJ?: DocumentType) => void] =
    useState<DocumentType>(null);

  // Pièce jointe à téléverser
  const [nouvellePieceJointe, setNouvellePieceJointe]: [
    File | null,
    (nouvellePieceJointe: File) => void,
  ] = useState(null);

  // Indique si la sauvegarde des notes de suivi est en cours
  const [sauvegardeEnCours, setSauvegarderEnCours]: [
    boolean,
    (mode: boolean) => void,
  ] = useState(false);

  const estTailleFichierOk = (fichier?: File) =>
    fichier && fichier.size < 10 * 1024 * 1024;
  const estTypeFichierOk = (fichier?: File) =>
    fichier && ["application/pdf"].includes(fichier.type);

  const ajouterPieceJointe = useCallback(
    async ({
      typePJ,
      nouvellePieceJointe,
    }: {
      typePJ: DocumentType;
      nouvellePieceJointe: File;
    }) => {
      setSauvegarderEnCours(true);

      const payload = new FormData();
      payload.append("file", nouvellePieceJointe);
      payload.append("type", typePJ.type);

      const response = await fetch(
        `/agent/redacteur/dossier/${dossier.id}/piece-jointe/ajouter.json`,
        {
          method: "POST",
          headers: {
            Accept: "application/json",
          },
          body: payload,
        },
      );

      if (response.ok) {
        const data = await response.json();
        const document = plainToInstance(Document, data);
        dossier.addDocument(document);
        _modale.close();
        onAjoute?.(document);
        refChampFichier.current.value = null;
        setTypePj(null);
        setNouvellePieceJointe(null);
      }

      setSauvegarderEnCours(false);
    },
    [dossier.id],
  );

  return (
    <>
      <ButtonsGroup
        inlineLayoutWhen="never"
        alignment="center"
        buttonsIconPosition="right"
        buttons={[
          {
            priority: "primary",
            iconId: "fr-icon-file-add-line",
            disabled: !(agent.estValidateur() || agent.instruit(dossier)),
            onClick: () => _modale.open(),
            children: "Ajouter",
          },
        ]}
      />

      <_modale.Component
        title=" Ajouter une pièce jointe au dossier"
        iconId="fr-icon-file-add-line"
        size="large"
      >
        <Select
          className="fr-my-2w"
          label="Type de pièce jointe"
          nativeSelectProps={{
            onChange: (e) =>
              setTypePj(
                Document.types.find(
                  (type: DocumentType) => type.type == e.target.value,
                ),
              ),
            value: typePJ?.type ?? "",
          }}
        >
          <option value="" disabled hidden>
            Sélectionnez un type
          </option>
          {Document.types
            .filter((type: DocumentType) => type.estAjoutableAgent())
            .map((type: DocumentType) => (
              <option value={type.type} key={type.type}>
                {type.libelle}
              </option>
            ))}
        </Select>

        <Upload
          className="fr-my-2w"
          label="Fichier à téléverser"
          hint="Taille maximale : 10 Mo. Format pdf uniquement."
          ref={refChampFichier}
          state={
            !nouvellePieceJointe ||
            estTailleFichierOk(nouvellePieceJointe) ||
            estTypeFichierOk(nouvellePieceJointe)
              ? "default"
              : "error"
          }
          disabled={sauvegardeEnCours || !typePJ}
          stateRelatedMessage=""
          nativeInputProps={{
            accept: "application/pdf,image/*",
            defaultValue: null,
            onChange: (e) => setNouvellePieceJointe(e.target.files[0]),
          }}
        />

        <ButtonsGroup
          className="fr-my-2w"
          inlineLayoutWhen="always"
          alignment="right"
          buttonsIconPosition="right"
          buttons={[
            {
              priority: "tertiary no outline",
              disabled: sauvegardeEnCours || !typePJ || !nouvellePieceJointe,
              children: "Annuler",
              onClick: () => _modale.close(),
            },
            {
              priority: "primary",
              disabled: sauvegardeEnCours || !typePJ || !nouvellePieceJointe,
              onClick: () =>
                ajouterPieceJointe({ typePJ, nouvellePieceJointe }),
              iconId: "fr-icon-file-add-line",
              children: "Ajouter",
            },
          ]}
        />
      </_modale.Component>
    </>
  );
};

export { component as AjoutPieceJointe };
