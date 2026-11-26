import { createModal } from "@codegouvfr/react-dsfr/Modal";
import React, { RefObject, useCallback, useRef, useState } from "react";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Dossier } from "@/apps/requerant/models";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { Upload } from "@codegouvfr/react-dsfr/Upload";

const _modale = createModal({
  id: "modale-signature-manuelle-dossier",
  isOpenedByDefault: false,
});

export const SignatureManuelleModale = ({
  dossier,
  onSigne,
}: {
  dossier: Dossier;
  onSigne: (fichierSigne: File) => Promise<void>;
}) => {
  // Références vers les conteneurs DOM de chacune des étapes du formulaire de signature
  const refEtapes: RefObject<HTMLDivElement | null>[] = [
    useRef<HTMLDivElement | null>(null),
    useRef<HTMLDivElement | null>(null),
    useRef<HTMLDivElement | null>(null),
  ];
  // Numéro de l'étape active sur le formulaire de signature
  const [etape, setEtape] = useState(0);

  // Fichier signé à téléverser
  const [fichierSigne, setFichierSigne]: [
    File | undefined,
    (fichierSigne: File) => void,
  ] = useState<File>();

  const estTailleFichierOk = (fichier?: File) =>
    !!fichier && fichier.size < 10 * 1024 * 1024;
  const estTypeFichierOk = (fichier?: File) =>
    !!fichier && ["application/pdf"].includes(fichier.type);

  // Indique si l'envoi du fichier, via l'API, est en cours
  const [sauvegarderEnCours, setSauvegarderEnCours]: [
    boolean,
    (mode: boolean) => void,
  ] = useState(false);

  const signerCourrier = useCallback(async () => {
    if (fichierSigne) {
      setSauvegarderEnCours(true);
      await onSigne(fichierSigne);

      _modale.close();
      setSauvegarderEnCours(false);
    }
  }, [dossier.id, fichierSigne]);

  return (
    <>
      <ButtonsGroup
        className="fr-col-lg-3 fr-col-offset-lg-9"
        inlineLayoutWhen="always"
        alignment="right"
        buttons={[
          {
            children: "Accepter la proposition",
            onClick: () => _modale.open(),
          },
        ]}
      />
      <_modale.Component
        title="Signature du dossier"
        iconId={"fr-icon-ball-pen-line"}
        size={"large"}
      >
        <Stepper
          currentStep={etape + 1}
          stepCount={refEtapes.length}
          title={refEtapes.at(etape)?.current?.getAttribute("data-titre")}
        />

        <div
          hidden={etape != 0}
          ref={refEtapes.at(0)}
          data-titre="Récupérer le document"
        >
          <p>
            Pour accepter la proposition d'indemnisation, vous allez devoir
            signer <i>manuellement</i> la déclaration d'acceptation, figurant en
            annexe du courrier. Pour cela nous recommandons d'utiliser un
            ordinateur.
          </p>

          <p>
            La première étape consiste à enregistrer le document sur votre
            disque-dur en cliquant sur le bouton "Télécharger le formulaire
            d'acceptation" ci-dessous, avant de pouvoir passer à l'étape
            suivante.
          </p>

          <div className="fr-input-group fr-mb-3w">
            <a
              className="fr-link fr-link--download"
              download={`Lettre décision dossier ${dossier.reference}`}
              href={dossier.getFormulaireDeclarationAcceptation()?.url}
            >
              Télécharger le formulaire d'acceptation
            </a>
          </div>

          <ButtonsGroup
            className="fr-mt-3w"
            buttonsSize="small"
            inlineLayoutWhen="always"
            alignment="right"
            buttons={[
              {
                children: "Annuler",
                priority: "tertiary no outline",
                onClick: () => {
                  _modale.close();
                  setEtape(0);
                },
              },
              {
                children: "Étape suivante",
                priority: "primary",
                onClick: () => setEtape(1),
              },
            ]}
          />
        </div>

        <div
          hidden={etape != 1}
          className={""}
          ref={refEtapes.at(1)}
          data-titre="Remplir et signer le formulaire"
        >
          <p>
            Pour compléter le formulaire, imprimez le document que vous venez de
            télécharger, remplissez le avec les informations concernant votre
            état civil ainsi que vos coordonnées bancaires *à jour* et
            signez-le.
          </p>

          <p>
            Une fois dûment complété, scannez-le pour récupérer la version
            électronique sous forme de fichier au format PDF et passez à l'étape
            suivante. Nous vous conseillons{" "}
            <a
              href="https://leclaireur.fnac.com/article/cp64397-comment-scanner-un-document-avec-votre-imprimante-ou-votre-telephone/"
              target="_blank"
              title="Article sur le site de la Fnac: comment scanner un document ?"
            >
              ce petit guide proposé par la Fnac
            </a>
            &nbsp;si vous souhaitez en savoir plus sur comment faire.
          </p>

          <ButtonsGroup
            className="fr-mt-3w"
            buttonsSize="small"
            inlineLayoutWhen="always"
            alignment="right"
            buttons={[
              {
                children: "Annuler",
                priority: "tertiary no outline",
                onClick: () => {
                  _modale.close();
                  setEtape(0);
                },
              },
              {
                children: "Étape précédente",
                priority: "secondary",
                onClick: () => setEtape(0),
              },

              {
                children: "Étape suivante",
                priority: "primary",
                onClick: () => setEtape(2),
              },
            ]}
          />
        </div>
        <div
          hidden={etape != 2}
          className={""}
          ref={refEtapes.at(2)}
          data-titre="Transmettre le document signé"
        >
          <p>
            Maintenant que la déclaration est dûment remplie et signée, il ne
            vous reste plus qu'à la transmettre au bureau du précontentieux en
            la téléversant sur la plateforme.
          </p>
          <p>
            Sélectionnez ci-dessous le fichier que vous venez d'éditer, puis
            validez en cliquant sur le bouton "Envoyer".
          </p>
          <Upload
            label="Téléverser le fichier pour accepter la proposition et être
                    indémnisé"
            hint="Taille maximale : 10 Mo, format pdf uniquement."
            state={
              !fichierSigne ||
              (estTypeFichierOk(fichierSigne) &&
                estTailleFichierOk(fichierSigne))
                ? "default"
                : "error"
            }
            stateRelatedMessage={
              !estTailleFichierOk(fichierSigne)
                ? "Le fichier dépasse les 10 Mo"
                : "Le fichier n'est pas au format PDF"
            }
            nativeInputProps={{
              accept: "application/pdf",
              onChange: (e) => {
                if (e.target.files?.length) {
                  setFichierSigne(e.target.files?.item(0) as File);
                }
              },
            }}
            multiple={false}
          />

          {/*
                  {erreurEnvoi && (
                    <Alert
                      className="fr-my-2w"
                      severity="error"
                      title="Erreur lors du téléversement"
                      description={erreurEnvoi}
                      closable={true}
                    />
                  )}
                  */}

          <ButtonsGroup
            inlineLayoutWhen="always"
            alignment="right"
            buttonsIconPosition="right"
            buttonsSize="small"
            buttons={[
              {
                children: "Annuler",
                priority: "tertiary no outline",
                disabled: sauvegarderEnCours,
                onClick: () => {
                  _modale.close();
                  setEtape(0);
                },
              },
              {
                children: "Étape précédente",
                priority: "secondary",
                disabled: sauvegarderEnCours,
                onClick: () => setEtape(1),
              },
              {
                children: "Envoyer",
                priority: "primary",
                disabled:
                  sauvegarderEnCours ||
                  !estTailleFichierOk(fichierSigne) ||
                  !estTypeFichierOk(fichierSigne),
                onClick: () => signerCourrier(),
              },
            ]}
          />
        </div>
      </_modale.Component>
    </>
  );
};
