import { PrevisualiserFichier } from "@/apps/requerant/composants/piecesJointes/PrevisualiserFichier.tsx";
import {
  Modale,
  ModaleProps,
  ModaleRef,
} from "@/common/composants/dsfr/Modale.tsx";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Select } from "@codegouvfr/react-dsfr/Select";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { Upload } from "@codegouvfr/react-dsfr/Upload";
import React, {
  ForwardedRef,
  forwardRef,
  useImperativeHandle,
  useMemo,
  useRef,
  useState,
} from "react";

export const AjouterPiecesJointesModaleEtapes = [
  "selectionner",
  "previsualiser",
] as const;
type AjouterPiecesJointesModaleEtape =
  (typeof AjouterPiecesJointesModaleEtapes)[number];

export type AjouterPiecesJointesModaleRef = {
  ouvrir: (etape?: AjouterPiecesJointesModaleEtape) => void;
};

export type AjouterPiecesJointesModaleProps = Omit<
  ModaleProps,
  "children" | "id"
> & {};

export const AjouterPiecesJointesModale = forwardRef<
  AjouterPiecesJointesModaleRef,
  AjouterPiecesJointesModaleProps
>(
  (
    props: AjouterPiecesJointesModaleProps,
    ref: ForwardedRef<AjouterPiecesJointesModaleRef>,
  ) => {
    const [etape, setEtape] =
      useState<AjouterPiecesJointesModaleEtape>("selectionner");

    const [fichiers, setFichiers] = useState<File[]>([]);
    const [indiceFichier, setIndiceFichier] = useState<number>(0);

    const fichierPrevisualise = useMemo(
      () => fichiers[indiceFichier],
      [indiceFichier, fichiers],
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
          <div
            className="fr-grid-row fr-grid-row--gutters fr-grid-row--center"
            style={{ gap: "1rem" }}
          >
            <div className="fr-col-12">
              <p>
                Veuillez sélectionner le ou les documents que vous souhaitez
                joindre à votre demande.
              </p>

              <Upload
                multiple={true}
                label={"Documents à téléverser"}
                hint={
                  "Taille maximale par fichier : 10 Mo. Formats supportés : jpg, png, webp, pdf. Plusieurs fichiers possibles."
                }
                nativeInputProps={{
                  onChange: (e) => {
                    setFichiers(Array.from(e.target.files ?? []));
                  },
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
                  disabled: fichiers.length === 0,
                  onClick: () => {
                    setEtape("previsualiser");
                  },
                },
              ]}
            />
          </div>
        )}

        {etape === "previsualiser" && (
          <>
            <div className="fr-col-12">
              {fichiers.length > 1 ? (
                <Stepper
                  currentStep={indiceFichier + 1}
                  stepCount={fichiers.length}
                  title={`Fichier n°${indiceFichier + 1} : ${fichiers[indiceFichier].name}`}
                  nextTitle={
                    indiceFichier + 1 < fichiers.length
                      ? `Fichier n°${indiceFichier + 2} : ${fichiers[indiceFichier + 1].name}`
                      : undefined
                  }
                />
              ) : (
                <h5>Fichier : {fichierPrevisualise.name}</h5>
              )}
            </div>
            <div className="fr-grid-row fr-grid-row--gutters">
              <div className="fr-col-lg-4 fr-col-12">
                <Select
                  label="Type de pièce jointe"
                  nativeSelectProps={{ defaultValue: "" }}
                >
                  <option value="1">Document</option>
                </Select>
              </div>
              <div className="fr-col-lg-8 fr-col-12">
                <PrevisualiserFichier fichier={fichierPrevisualise} />
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
                  ...(indiceFichier > 0
                    ? [
                        {
                          priority: "secondary",
                          iconId: "fr-icon-arrow-left-line",
                          iconPosition: "left",
                          children: "Document précédent",
                          onClick: () =>
                            setIndiceFichier(
                              (indiceFichier) => indiceFichier - 1,
                            ),
                        } as ButtonProps,
                      ]
                    : []),
                  {
                    priority: "primary",
                    iconId:
                      indiceFichier + 1 === fichiers.length
                        ? "fr-icon-upload-line"
                        : "fr-icon-arrow-right-line",
                    children:
                      indiceFichier + 1 === fichiers.length
                        ? `Téléverser ce${fichiers.length > 1 ? "s" : ""} document${fichiers.length > 1 ? "s" : ""}`
                        : "Document suivant",
                    onClick:
                      indiceFichier + 1 === fichiers.length
                        ? () => {
                            console.log("Téléverser le(s) document(s)");
                            refModale.current?.fermer();
                            // On délaie la réinitialisation de la modale le temps qu'elle disparaisse
                            setInterval(() => {
                              setEtape("selectionner");
                              setFichiers([]);
                            }, 500);
                          }
                        : () => {
                            setIndiceFichier(
                              (indiceFichier) => indiceFichier + 1,
                            );
                          },
                  } as ButtonProps,
                ]}
              />
            </div>
          </>
        )}
      </Modale>
    );
  },
);
