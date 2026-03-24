import { FormSelect } from "@/apps/requerant/composants/champs/form/FormSelect.tsx";
import { FormUpload } from "@/apps/requerant/composants/champs/form/FormUpload.tsx";
import { PrevisualiserFichier } from "@/apps/requerant/composants/piecesJointes/PrevisualiserFichier.tsx";
import { Dossier } from "@/apps/requerant/models";
import { PieceJointeType, TypePieceJointe } from "@/apps/requerant/models/TypePieceJointe.ts";
import { NouvellePieceJointe } from "@/apps/requerant/services/DossierManager.ts";
import { Modale, ModaleProps, ModaleRef } from "@/common/composants/dsfr/Modale.tsx";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { useForm } from "@tanstack/react-form";
import React, { ForwardedRef, forwardRef, useImperativeHandle, useMemo, useRef, useState } from "react";
import { z } from "zod";

export const AjouterPiecesJointesModaleEtapes = [
  "selectionner",
  "previsualiser",
] as const;
type AjouterPiecesJointesModaleEtape =
  (typeof AjouterPiecesJointesModaleEtapes)[number];

const SchemaValidationSelectionFichiers = z.object({
  fichiers: z
    .array(z.instanceof(File))
    .min(1, { error: "Veuillez sélectionner au moins un fichier" })
    .max(10, {
      error: "Veuillez sélectionner jusqu'à 10 fichiers au maximum",
    })
    .superRefine((fichiers: File[], contexte) => {
      fichiers
        .filter(
          (fichier) =>
            ![
              "application/pdf",
              "image/jpg",
              "image/jpeg",
              "image/png",
              "image/webp",
            ].includes(fichier.type),
        )
        .forEach((fichier) => {
          contexte.addIssue({
            code: "custom",
            message: `Le format du fichier "${fichier.name}" n'est pas valide (jpg, png, webp, pdf)`,
          });
        });
      fichiers
        .filter((fichier) => fichier.size > 5_000_000)
        .forEach((fichier) => {
          contexte.addIssue({
            code: "custom",
            message: `La taille du fichier "${fichier.name}" excède 5 Mo`,
          });
        });
    }),
});

const SelectionnerFichierFormulaire = ({
  onSelectionne,
}: {
  onSelectionne: (fichiers: File[]) => void;
}) => {
  const formulaire = useForm({
    validators: {
      onSubmit: SchemaValidationSelectionFichiers,
      onChange: SchemaValidationSelectionFichiers,
    },
    defaultValues: { fichiers: [] as File[] },
    onSubmit: ({ value: { fichiers }, formApi }) => {
      if (formApi.state.isValid) {
        onSelectionne(fichiers);
      }
    },
  });

  return (
    <form
      onSubmit={async (e) => {
        e.preventDefault();
        e.stopPropagation();
        try {
          console.log(formulaire.validate("submit"));
          await formulaire.handleSubmit();
        } catch (e) {
          console.error(e);
        }
      }}
    >
      <div
        className="fr-grid-row fr-grid-row--gutters fr-grid-row--center"
        style={{ gap: "1rem" }}
      >
        <div className="fr-col-12">
          <p>
            Veuillez sélectionner le ou les documents que vous souhaitez joindre
            à votre demande.
          </p>

          <formulaire.Field
            name={"fichiers"}
            children={(field) => {
              return (
                <FormUpload
                  multiple={true}
                  label={"Documents à téléverser"}
                  hint={
                    <span>
                      Taille maximale par fichier : 5 Mo. <br />
                      Formats supportés : jpg, png, webp, pdf.
                      <br /> Plusieurs fichiers possibles, jusqu'à 10 maximum.
                    </span>
                  }
                  nativeInputProps={{
                    onChange: (e) => {
                      field.setValue(Array.from(e.target.files ?? []));
                    },
                  }}
                  estRequis={true}
                  champ={field}
                />
              );
            }}
          />
        </div>

        <ButtonsGroup
          className="fr-col-12"
          inlineLayoutWhen="always"
          alignment="right"
          buttonsIconPosition="right"
          buttonsSize="small"
          buttons={[
            {
              children: "Prévisualiser",
              iconId: "fr-icon-eye-line",
              nativeButtonProps: {
                role: "submit",
                type: "submit",
              },
            },
          ]}
        />
      </div>
    </form>
  );
};

const SchemaValidationPrevisualisationFichiers = z.object({
  fichier: z.instanceof(File),
  type: z.instanceof(TypePieceJointe, {
    error: "Veuillez sélectionner un type de pièce jointe valide",
  }),
});

const PrevisualiserFichierFormulaire = ({
  pieceJointe,
  indice,
  nbAjouts,
  pieceJointeSuivante,
  onRevenir,
  onValide,
}: {
  pieceJointe: NouvellePieceJointe;
  indice: number;
  nbAjouts: number;
  pieceJointeSuivante?: NouvellePieceJointe;
  onRevenir: () => void;
  onValide: (
    pieceJointe: NouvellePieceJointe,
    indice: number,
    estDerniere: boolean,
  ) => void | Promise<void>;
}) => {
  const s = useMemo(() => (nbAjouts > 1 ? "s" : ""), [nbAjouts]);

  const estDerniere = useMemo(
    () => indice + 1 === nbAjouts,
    [indice, nbAjouts],
  );

  const [televersementEnCours, setTeleversementEnCours] =
    useState<boolean>(false);

  const formulaire = useForm({
    defaultValues: pieceJointe,
    validators: {
      onSubmit: SchemaValidationPrevisualisationFichiers,
      onChange: SchemaValidationPrevisualisationFichiers,
    },
    onSubmit: async ({ value, formApi }) => {
      if (formApi.state.isValid) {
        if (estDerniere) {
          setTeleversementEnCours(true);
        }
        await onValide(value, indice, estDerniere);
        setTeleversementEnCours(false);
      }
    },
  });

  return (
    <form
      onSubmit={async (e) => {
        e.preventDefault();
        e.stopPropagation();
        try {
          await formulaire.handleSubmit();
        } catch (e) {
          console.error(e);
        }
      }}
    >
      <div className="fr-col-12">
        {nbAjouts > 1 ? (
          <Stepper
            currentStep={indice + 1}
            stepCount={nbAjouts}
            title={`Fichier n°${indice + 1} : ${pieceJointe.fichier.name}`}
            nextTitle={
              pieceJointeSuivante
                ? `Fichier n°${indice + 2} : ${pieceJointeSuivante.fichier.name}`
                : undefined
            }
          />
        ) : (
          <h5>Fichier : {pieceJointe.fichier.name}</h5>
        )}
      </div>
      <div className="fr-grid-row fr-grid-row--gutters">
        <div className="fr-col-lg-4 fr-col-12">
          <formulaire.Field
            name="type"
            key={`piece-jointe-${indice}-${pieceJointe.fichier.name}`}
            children={(field) => (
              <FormSelect
                label="Type de pièce jointe"
                nativeSelectProps={{
                  defaultValue: pieceJointe.type?.type ?? "",
                  onChange: (e) => {
                    field.setValue(
                      TypePieceJointe.depuis(e.target.value as PieceJointeType),
                    );
                  },
                }}
                champ={field}
                estRequis={true}
              >
                <option value="">Sélectionnez un type</option>

                {Object.values(TypePieceJointe.liste).map((type) => (
                  <option key={type.type} value={type.type}>
                    {type.libelle}
                  </option>
                ))}
              </FormSelect>
            )}
          />
        </div>
        <div className="fr-col-lg-8 fr-col-12">
          <PrevisualiserFichier fichier={pieceJointe.fichier} />
        </div>
      </div>

      <div className="fr-col-12">
        <ButtonsGroup
          inlineLayoutWhen="always"
          alignment="right"
          buttonsIconPosition="right"
          buttonsSize="small"
          // @ts-ignore
          buttons={[
            ...(indice > 0
              ? [
                  {
                    priority: "secondary",
                    iconId: "fr-icon-arrow-left-line",
                    disabled: televersementEnCours,
                    iconPosition: "left",
                    children: "Document précédent",
                    onClick: () => onRevenir(),
                  } as ButtonProps,
                ]
              : []),
            {
              priority: "primary",
              nativeButtonProps: {
                role: "submit",
                type: "submit",
              },
              iconId:
                indice + 1 === nbAjouts
                  ? "fr-icon-upload-line"
                  : "fr-icon-arrow-right-line",
              disabled: televersementEnCours,
              children: televersementEnCours
                ? "Téléversement en cours ..."
                : estDerniere
                  ? `Téléverser ce${s} document${s}`
                  : "Document suivant",
            } as ButtonProps,
          ]}
        />
      </div>
    </form>
  );
};

export type AjouterPiecesJointesModaleRef = {
  ouvrir: (etape?: AjouterPiecesJointesModaleEtape) => void;
};

export type AjouterPiecesJointesModaleProps = Omit<
  ModaleProps,
  "children" | "id"
> & {
  dossier: Dossier;
  onComplete: (piecesJointes: NouvellePieceJointe[]) => Promise<void>;
};

export const AjouterPiecesJointesModale = forwardRef<
  AjouterPiecesJointesModaleRef,
  AjouterPiecesJointesModaleProps
>(
  (
    { dossier, onComplete, ...props }: AjouterPiecesJointesModaleProps,
    ref: ForwardedRef<AjouterPiecesJointesModaleRef>,
  ) => {
    const [etape, setEtape] =
      useState<AjouterPiecesJointesModaleEtape>("selectionner");

    const [piecesJointes, setPiecesJointes] = useState<NouvellePieceJointe[]>(
      [],
    );
    const [indicePieceJointePrevisualisee, setIndicePieceJointePrevisualisee] =
      useState<number>(0);

    const pieceJointePrevisualisee = useMemo(
      () => piecesJointes[indicePieceJointePrevisualisee],
      [piecesJointes, indicePieceJointePrevisualisee],
    );

    const refModale = useRef<ModaleRef>(null);

    useImperativeHandle(ref, () => ({
      ouvrir: (etape?: AjouterPiecesJointesModaleEtape) => {
        setEtape(etape ?? "selectionner");
        refModale.current?.ouvrir();
      },
    }));

    return (
      <Modale
        {...props}
        id="ajouter-pieces-jointes-modale"
        ref={refModale}
        size={etape == "selectionner" ? "medium" : "full"}
      >
        {etape === "selectionner" && (
          <SelectionnerFichierFormulaire
            onSelectionne={(fichiers: File[]) => {
              setPiecesJointes(
                fichiers.map((f) => ({ fichier: f }) as NouvellePieceJointe),
              );
              setEtape("previsualiser");
            }}
          />
        )}

        {etape === "previsualiser" && (
          <PrevisualiserFichierFormulaire
            pieceJointe={pieceJointePrevisualisee}
            indice={indicePieceJointePrevisualisee}
            nbAjouts={piecesJointes.length}
            onRevenir={() =>
              setIndicePieceJointePrevisualisee(
                (indicePieceJointePrevisualisee) =>
                  Math.max(indicePieceJointePrevisualisee - 1, 0),
              )
            }
            onValide={async (
              pieceJointe: NouvellePieceJointe,
              indice: number,
              estDerniere: boolean,
            ) => {
              setPiecesJointes((piecesJointes) =>
                piecesJointes.map((pj, i) => (i === indice ? pieceJointe : pj)),
              );
              if (estDerniere) {
                await onComplete(
                  piecesJointes.map((pj, i) =>
                    i === indice ? pieceJointe : pj,
                  ),
                );
                refModale.current?.fermer();
              } else {
                setIndicePieceJointePrevisualisee(
                  (indicePieceJointePrevisualisee) =>
                    Math.min(
                      indicePieceJointePrevisualisee + 1,
                      piecesJointes.length - 1,
                    ),
                );
              }
            }}
          />
        )}
      </Modale>
    );
  },
);
