import React, {
  ClipboardEvent,
  FormEvent,
  useCallback,
  useRef,
  useState,
} from "react";
import { Agent, Document, DocumentType, DossierDetail } from "@/common/models";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { plainToInstance } from "class-transformer";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Upload } from "@codegouvfr/react-dsfr/Upload";
import { Select } from "@codegouvfr/react-dsfr/Select";
import { Input } from "@codegouvfr/react-dsfr/Input";
import { useInjection } from "inversify-react";
import { DossierManagerInterface } from "@/common/services/agent/dossier.ts";

const _modale = createModal({
  id: "modale-ajouter-piece-jointe",
  isOpenedByDefault: false,
});

type EtatAjout = "choix_type" | "televersement";

const component = function AjoutPieceJointe({
  dossier,
  agent,
  onAjoute,
}: {
  dossier: DossierDetail;
  agent: Agent;
  onAjoute?: (nouvellePieceJointe: Document) => void;
}) {
  // DossierManager
  const dossierManager = useInjection<DossierManagerInterface>(
    DossierManagerInterface.$,
  );

  // Ref sur le champ de sélection du fichier, pour pouvoir le réinitialiser
  const refChampFichier = useRef<HTMLInputElement>(null);

  // Type de document associé à la pièce jointe téléversée
  const [typePJ, setTypePj]: [
    DocumentType | null,
    (typePJ: DocumentType | null) => void,
  ] = useState<DocumentType | null>(null);

  // Pièce jointe à téléverser
  const [nouvellePieceJointe, setNouvellePieceJointe]: [
    File | null,
    (nouvellePieceJointe: File | null) => void,
  ] = useState<File | null>(null);

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

      const document = await dossierManager.ajouterPieceJointe(
        dossier,
        typePJ,
        nouvellePieceJointe,
      );
      dossier.addDocument(document);

      _modale.close();
      onAjoute?.(document);
      if (refChampFichier.current) refChampFichier.current.value = "";
      setTypePj(null);
      setNouvellePieceJointe(null);
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
            disabled: !(agent.estValidateur() || agent.estRedacteur()),
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
                ) as DocumentType,
              ),
            value: typePJ?.type ?? "",
          }}
        >
          <option value="" disabled hidden>
            Sélectionnez un type
          </option>
          {Document.types
            .filter(
              (type: DocumentType) =>
                ![
                  DocumentType.TYPE_COURRIER_MINISTERE,
                  DocumentType.TYPE_ARRETE_PAIEMENT,
                ].includes(type),
            )
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
            defaultValue: "",
            onChange: (e) =>
              setNouvellePieceJointe(e.target.files?.item(0) ?? null),
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
              onClick: () => {
                if (nouvellePieceJointe) {
                  ajouterPieceJointe({
                    typePJ: typePJ as DocumentType,
                    nouvellePieceJointe,
                  });
                }
              },
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
