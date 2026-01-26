import { createModal } from "@codegouvfr/react-dsfr/Modal";
import React, { useCallback, useState } from "react";
import { Upload } from "@codegouvfr/react-dsfr/Upload";
import { Select } from "@codegouvfr/react-dsfr/Select";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { useForm } from "@tanstack/react-form";
import { Document, DocumentType } from "@/common/models";
import { DeclarationFDOBrisPorte } from "@/apps/agent/fdo/models/DeclarationFDOBrisPorte.ts";
import { plainToInstance } from "class-transformer";

type MimeTypePieceJointe =
  | "application/pdf"
  | "image/jpeg"
  | "image/png"
  | "image/webp";

const toBase64 = (file: File) =>
  new Promise<{ mime: MimeTypePieceJointe; base64: string }>(
    (resolve, reject) => {
      const reader = new FileReader();
      reader.readAsDataURL(file);
      reader.onload = () =>
        resolve({
          mime: file.type as MimeTypePieceJointe,
          base64: reader.result as string,
        });
      reader.onerror = reject;
    },
  );

const estPdf = async (fichier: Document | File): Promise<boolean> => {
  if (fichier instanceof Document) {
    return fichier.estPDF();
  }

  return (await toBase64(fichier)).mime === "application/pdf";
};

const _modale = createModal({
  id: "modale-ajouter-fichier-declaration-bris-porte",
  isOpenedByDefault: false,
});

export interface ModaleAjoutPieceJointeRef {
  ouvrir: () => void;
  fermer: () => void;
}

export interface ModaleAjoutPieceJointeProps {
  declarationFDO: DeclarationFDOBrisPorte;
  pieceJointe?: Document;
  onTeleverse: (declaration: DeclarationFDOBrisPorte) => void | Promise<void>;
}

export const ModaleAjoutPieceJointe = React.forwardRef<
  ModaleAjoutPieceJointeRef,
  ModaleAjoutPieceJointeProps
>(
  (
    { pieceJointe, declarationFDO, onTeleverse },
    ref: React.Ref<ModaleAjoutPieceJointeRef>,
  ) => {
    // Exposer les fonctions au composant appelant
    React.useImperativeHandle(ref, () => ({
      ouvrir: () => _modale.open(),
      fermer: () => _modale.close(),
    }));

    const form = useForm({
      defaultValues: {
        id: pieceJointe?.id,
        type: pieceJointe?.type,
        fichier: pieceJointe,
      } as {
        id?: number;
        type?: DocumentType;
        fichier?: Document | File | undefined;
      },
    });

    const [sauvegardeEnCours, setSauvegardeEnCours] = useState<boolean>(false);

    const televerser = useCallback(
      async ({ type, fichier }: { type: DocumentType; fichier: File }) => {
        setSauvegardeEnCours(true);

        const payload = new FormData();
        payload.append("pieceJointe", fichier);

        const response = await fetch(
          `/api/agent/fdo/bris-de-porte/${declarationFDO.id}/televerser-piece-jointe/${type.type}`,
          {
            method: "POST",
            body: payload,
          },
        );

        const data = await response.json();

        if (response.ok) {
          await onTeleverse(plainToInstance(DeclarationFDOBrisPorte, data));
        } else {
          // TODO : afficher un message d'erreur
        }

        setSauvegardeEnCours(false);
        _modale.close();
      },
      [declarationFDO],
    );

    return (
      <_modale.Component
        size="large"
        title="Ajouter un document"
        concealingBackdrop={!sauvegardeEnCours}
      >
        <form
          className="fr-grid-row fr-grid-row--gutters"
          onSubmit={(e) => {
            e.preventDefault();
            e.stopPropagation();

            console.log(form.state.values);
            televerser({
              type: form.state.values.type as DocumentType,
              fichier: form.state.values.fichier as File,
            });
          }}
        >
          <form.Field
            name="type"
            children={(field) => (
              <Select
                label="Type de document"
                className="fr-col-12"
                nativeSelectProps={{
                  onChange: (e) => {
                    field.setValue(
                      Document.typesFDO.find(
                        (type: DocumentType) => type.type == e.target.value,
                      ),
                    );
                  },
                }}
              >
                <option value="">Sélectionnez un type</option>

                {Document.typesFDO.map((type: DocumentType) => (
                  <option
                    key={`type-piece-jointe-${type.type}`}
                    value={type.type}
                  >
                    {type.libelleFDO()}
                  </option>
                ))}
              </Select>
            )}
          />

          <form.Field
            name="fichier"
            children={(field) => (
              <Upload
                className="fr-col-12"
                label="Fichier à téléverser"
                hint="Taille maximale : 10 Mo. Format pdf uniquement."
                nativeInputProps={{
                  accept: "application/pdf,image/*",
                  onChange: (e) => {
                    field.setValue(e.target.files?.item(0) ?? undefined);
                  },
                }}
              />
            )}
          />

          <form.Subscribe
            selector={(state) => [state.values.type, state.values.fichier]}
            children={([type, fichier]) => (
              <ButtonsGroup
                className="fr-col-12"
                inlineLayoutWhen="always"
                alignment="right"
                buttonsIconPosition="left"
                buttonsEquisized={false}
                buttonsSize="small"
                buttons={[
                  {
                    children: "Annuler",
                    priority: "secondary",
                    disabled: sauvegardeEnCours,
                    onClick: _modale.close,
                    nativeButtonProps: {
                      type: "button",
                    },
                  },
                  {
                    children: "Ajouter",
                    priority: "primary",
                    disabled: sauvegardeEnCours || !type || !fichier,
                    nativeButtonProps: {
                      type: "submit",
                    },
                  },
                ]}
              />
            )}
          />
        </form>
      </_modale.Component>
    );
  },
);
