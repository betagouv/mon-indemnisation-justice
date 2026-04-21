import { NonTrouveComposant } from "@/apps/requerant/composants/routeur/NonTrouveComposant.tsx";
import { container } from "@/apps/requerant/container.ts";
import { AfficherPieceJointe } from "@/apps/requerant/dossier/components/PieceJointe/AfficherPieceJointe.tsx";
import {
  Dossier,
  getRapportAuLogementLibelle,
  PieceJointe,
} from "@/apps/requerant/models";
import { RouteurRequerant } from "@/apps/requerant/routeur";
import { DossierManagerInterface } from "@/apps/requerant/services/DossierManager.ts";
import { Loader } from "@/common/composants/Loader.tsx";
import { Document } from "@/common/models";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { FrIconClassName } from "@codegouvfr/react-dsfr/fr/generatedFromCss/classNames";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import Tabs from "@codegouvfr/react-dsfr/Tabs";
import { Upload } from "@codegouvfr/react-dsfr/Upload";
import {
  createFileRoute,
  notFound,
  NotFoundRouteProps,
  useRouter,
} from "@tanstack/react-router";
import { useInjection } from "inversify-react";
import React, { RefObject, useCallback, useRef, useState } from "react";

const signatureModal = createModal({
  id: "modale-signature-dossier",
  isOpenedByDefault: false,
});

const urlDocumentRequerant = (document: Document) =>
  `/requerant/document/${document.id}/${document.filename}`;

export const Route = createFileRoute(
  "/requerant/dossier/bris-de-porte/$id/consulter-la-decision",
)({
  component: ConsulterDecisionBrisPorte,
  shouldReload: true,
  params: {
    parse: ({ id }) => ({ id: parseInt(id) }),
    stringify: ({ id }) => ({ id: id.toString() }),
  },
  pendingComponent: Loader,
  notFoundComponent: (props: NotFoundRouteProps) => (
    <NonTrouveComposant {...props} />
  ),
  loader: async ({ params }) => {
    const dossier = await container
      .get<DossierManagerInterface>(DossierManagerInterface.$)
      .getDossier(params.id);

    if (!dossier) {
      throw notFound({
        data: {
          titre: "Impossible de trouver le dossier",
          message: (
            <>
              Le dossier n°<i>${params.id}</i>n'existe pas ou ne vous est pas
              accessible.
            </>
          ),
        },
        throw: true,
      });
    }

    return { dossier };
  },
});

function ConsulterDecisionBrisPorte() {
  const dossierManager = useInjection<DossierManagerInterface>(
    DossierManagerInterface.$,
  );

  // Récupération du routeur, uniquement pour pouvoir invalider son cache
  const routeur = useRouter<typeof RouteurRequerant>();

  // Récupérer la référence depuis le paramètre de la route
  const { dossier }: { dossier: Dossier } = Route.useLoaderData();

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
      await dossierManager.accepter(dossier.id, fichierSigne);
      routeur.invalidate();

      signatureModal.close();
      setSauvegarderEnCours(false);
    }
  }, [dossier.id, fichierSigne]);

  return (
    <div className="fr-container">
      <div className="fr-grid-row">
        <div className="fr-col-12">
          <h1>Votre demande d'indemnisation</h1>

          <p>
            La demande d'indemnisation
            {dossier.dateDepot && (
              <>
                {" "}
                que vous avez initiée le
                {dossier.dateDepot.toLocaleString("fr-FR", {
                  weekday: "long",
                  day: "numeric",
                  month: "long",
                  year: "numeric",
                })}{" "}
              </>
            )}
            , concernant un bris de porte survenu{" "}
            {dossier.dateOperation
              ? "le " +
                dossier.dateOperation.toLocaleString("fr-FR", {
                  weekday: "long",
                  day: "numeric",
                  month: "long",
                  year: "numeric",
                })
              : " à une date inconnue"}{" "}
            au logement{" "}
            {dossier.adresse ? <>situé {dossier.adresse.libelle}</> : ""}
            {dossier.rapportAuLogement &&
              `, dont vous êtes ${
                dossier.rapportAuLogement !== "AUTRE"
                  ? getRapportAuLogementLibelle(
                      dossier.rapportAuLogement,
                    ).toLocaleLowerCase()
                  : (dossier.descriptionRapportAuLogement?.toLocaleLowerCase() ??
                    "")
              }`}
            , a été {dossier.estAccepte ? "acceptée" : "refusée"}.
          </p>

          <p>
            Vous trouverez ci-dessous le courrier expliquant les motivations de
            cette décision.
          </p>

          {dossier.estAccepte && (
            <p>
              Le montant de l'indemnisation qui vous est proposé est de{" "}
              <span className="fr-text--bold">
                {(dossier.montantIndemnisation as number).toLocaleString()} €
              </span>
              . Pour accepter la proposition et déclencher le versement de cette
              somme, il vous faut signer le document et nous le retourner en
              cliquant sur le bouton "Accepter la proposition".
            </p>
          )}
        </div>

        {dossier.estAccepte &&
          (dossier.estAccepteRequerant ? (
            <>
              <div className="fr-col-12 fr-my-2w">
                <Alert
                  severity="info"
                  title="Indemnisation acceptée"
                  description="Vous avez accepté la proposition d'indemnisation. Le virement du montant correspondant vers vore compte bancaire devrait être exécuté prochainement"
                  closable={false}
                />
              </div>
            </>
          ) : (
            <>
              <div className="fr-col-lg-3 fr-col-offset-lg-9">
                <ul className="fr-btns-group fr-btns-group--right">
                  <li>
                    <button
                      className="fr-btn"
                      onClick={() => signatureModal.open()}
                    >
                      Accepter la proposition
                    </button>
                  </li>
                </ul>
              </div>
              <signatureModal.Component
                title="Signature du dossier"
                iconId={"fr-icon-ball-pen-line"}
                size={"large"}
              >
                <Stepper
                  currentStep={etape + 1}
                  stepCount={refEtapes.length}
                  title={refEtapes
                    .at(etape)
                    ?.current?.getAttribute("data-titre")}
                />

                <div
                  hidden={etape != 0}
                  ref={refEtapes.at(0)}
                  data-titre="Récupérer le document"
                >
                  <p>
                    Pour accepter la proposition d'indemnisation, vous allez
                    devoir signer <i>électroniquement</i> la déclaration
                    d'acceptation, figurant en annexe du courrier. Pour cela
                    nous recommandons d'utiliser un ordinateur.
                  </p>

                  <p>
                    La première étape consiste à enregistrer le document sur
                    votre disque-dur en cliquant sur le bouton "Télécharger le
                    formulaire d'acceptation" ci-dessous, avant de pouvoir
                    passer à l'étape suivante.
                  </p>

                  <div className="fr-input-group fr-mb-3w">
                    <a
                      className="fr-link fr-link--download"
                      download={`Lettre décision dossier ${dossier.reference}`}
                      href={dossier.getDeclarationAcceptation()?.url}
                    >
                      Télécharger le formulaire d'acceptation
                    </a>
                  </div>

                  <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
                    <li>
                      <button
                        className="fr-btn fr-btn--sm fr-btn--tertiary-no-outline"
                        type="button"
                        onClick={() => {
                          signatureModal.close();
                          setEtape(0);
                        }}
                      >
                        Annuler
                      </button>
                    </li>
                    <li>
                      <button
                        className="fr-btn fr-btn--sm fr-btn--primary"
                        type="button"
                        onClick={() => setEtape(1)}
                      >
                        Étape suivante
                      </button>
                    </li>
                  </ul>
                </div>

                <div
                  hidden={etape != 1}
                  className={""}
                  ref={refEtapes.at(1)}
                  data-titre="Remplir et signer le formulaire"
                >
                  <p>
                    Pour compléter le formulaire, imprimez le document que vous
                    venez de télécharger, remplissez le avec les informations
                    concernant votre état civil ainsi que vos coordonnées
                    bancaires *à jour* et signez-le.
                  </p>

                  <p>
                    Une fois dûment complété, scannez-le pour récupérer la
                    version électronique sous forme de fichier au format PDF et
                    passez à l'étape suivante. Nous vous conseillons{" "}
                    <a
                      href="https://leclaireur.fnac.com/article/cp64397-comment-scanner-un-document-avec-votre-imprimante-ou-votre-telephone/"
                      target="_blank"
                      title="Article sur le site de la Fnac: comment scanner un document ?"
                    >
                      ce petit guide proposé par la Fnac
                    </a>
                    &nbsp;si vous souhaitez en savoir plus sur comment faire.
                  </p>

                  <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
                    <li>
                      <button
                        className="fr-btn fr-btn--sm fr-btn--tertiary-no-outline"
                        type="button"
                        onClick={() => {
                          signatureModal.close();
                          setEtape(0);
                        }}
                      >
                        Annuler
                      </button>
                    </li>
                    <li>
                      <button
                        className="fr-btn fr-btn--sm fr-btn--secondary"
                        type="button"
                        onClick={() => setEtape(0)}
                      >
                        Étape précédente
                      </button>
                    </li>
                    <li>
                      <button
                        className="fr-btn fr-btn--sm fr-btn--primary"
                        type="button"
                        onClick={() => setEtape(2)}
                      >
                        Étape suivante
                      </button>
                    </li>
                  </ul>
                </div>
                <div
                  hidden={etape != 2}
                  className={""}
                  ref={refEtapes.at(2)}
                  data-titre="Transmettre le document signé"
                >
                  <p>
                    Maintenant que la déclaration est dûment remplie et signée,
                    il ne vous reste plus qu'à la transmettre au bureau du
                    précontentieux en la téléversant sur la plateforme.
                  </p>
                  <p>
                    Sélectionnez ci-dessous le fichier que vous venez d'éditer,
                    puis validez en cliquant sur le bouton "Envoyer".
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
                          signatureModal.close();
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
              </signatureModal.Component>
            </>
          ))}

        <div className="fr-col-12">
          <Tabs
            tabs={[
              {
                label: dossier.estAccepte
                  ? "Proposition d'indemnisation"
                  : "Lettre de décision",
                iconId: "fr-icon-checkbox-circle-line",
                isDefault: true,
                content: dossier.getCourrierDecision() && (
                  <AfficherPieceJointe
                    pieceJointe={dossier.getCourrierDecision() as PieceJointe}
                    telecharger={false}
                  />
                ),
              },
              ...(dossier.estAccepte
                ? [
                    {
                      label: "Déclaration d'acceptation",
                      isDefault: false,
                      iconId: "fr-icon-chat-check-line" as FrIconClassName,
                      content: dossier.getDeclarationAcceptation() && (
                        <AfficherPieceJointe
                          pieceJointe={
                            dossier.getDeclarationAcceptation() as PieceJointe
                          }
                          telecharger={false}
                        />
                      ),
                    },
                  ]
                : []),
            ]}
          />
        </div>
      </div>
    </div>
  );
}
