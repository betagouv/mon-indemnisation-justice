import {
  AjouterPiecesJointesModale,
  AjouterPiecesJointesModaleRef,
} from "@/apps/requerant/composants/piecesJointes/AjouterPiecesJointesModale.tsx";
import { NonTrouveComposant } from "@/apps/requerant/composants/routeur/NonTrouveComposant";
import { container } from "@/apps/requerant/container";
import { Dossier, PieceJointe } from "@/apps/requerant/models";
import { TypePieceJointe } from "@/apps/requerant/models/TypePieceJointe.ts";
import {
  DossierManagerInterface,
  NouvellePieceJointe,
} from "@/apps/requerant/services/DossierManager";
import classes from "@/apps/requerant/style/form.module.css";
import { Loader } from "@/common/composants/Loader";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import Badge from "@codegouvfr/react-dsfr/Badge";
import { Button } from "@codegouvfr/react-dsfr/Button";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import Download from "@codegouvfr/react-dsfr/Download";
import artworkOvoid from "@codegouvfr/react-dsfr/dsfr/artwork/background/ovoid.svg?url";
import artworkDocumentAddUrl from "@codegouvfr/react-dsfr/dsfr/artwork/pictograms/document/document-add.svg?url&no-inline";
import SideMenu from "@codegouvfr/react-dsfr/SideMenu";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import {
  createFileRoute,
  notFound,
  NotFoundRouteProps,
  useNavigate,
  useRouter,
} from "@tanstack/react-router";
import { useInjection } from "inversify-react";
import { default as React, useRef, useState } from "react";

export const Route = createFileRoute(
  "/requerant/dossier/bris-de-porte/$reference/3-pieces-jointes",
)({
  component: Etape3PiecesJointes,
  pendingComponent: Loader,
  notFoundComponent: (props: NotFoundRouteProps) => (
    <NonTrouveComposant {...props} />
  ),
  loader: async ({ params }) => {
    const dossier = await container
      .get<DossierManagerInterface>(DossierManagerInterface.$)
      .getDossier(params.reference);

    if (!dossier) {
      throw notFound({
        data: {
          titre: "Impossible de trouver le dossier",
          message: (
            <>
              Le dossier de référence <i>${params.reference}</i>n'existe pas ou
              ne vous est pas accessible.
            </>
          ),
        },
        throw: true,
      });
    }

    return { reference: params.reference, dossier };
  },
  shouldReload: true,
});

function Etape3PiecesJointes() {
  const naviguer = useNavigate({
    from: Route.fullPath,
  });

  const dossierManager = useInjection<DossierManagerInterface>(
    DossierManagerInterface.$,
  );

  // Récupérer la référence depuis le paramètre de la route
  const { reference, dossier }: { reference: string; dossier: Dossier } =
    Route.useLoaderData();

  const router = useRouter();

  const refModaleAjoutPieceJointe = useRef<AjouterPiecesJointesModaleRef>(null);

  const [pieceJointe, setPieceJointe] = useState<PieceJointe | undefined>(
    dossier.piecesJointes.at(0),
  );

  const [typePieceJointe, setTypePieceJointe] = useState<TypePieceJointe>(
    pieceJointe?.type || TypePieceJointe.depuis("attestation_information"),
  );

  return (
    <>
      <AjouterPiecesJointesModale
        ref={refModaleAjoutPieceJointe}
        dossier={dossier}
        onComplete={async (
          piecesJointes: NouvellePieceJointe[],
        ): Promise<void> => {
          await dossierManager.ajouterPiecesJointes(reference, piecesJointes);
          await router.invalidate();
        }}
        title="Ajouter des pièces jointes"
        titleAs="h4"
        iconId={"fr-icon-add-line"}
        size="medium"
      />

      <h1>Déclarer un bris de porte</h1>

      <form
        onSubmit={async (e) => {
          e.preventDefault();
          e.stopPropagation();
        }}
        className={classes.mijForm}
      >
        <section>
          <Stepper
            currentStep={3}
            stepCount={3}
            title={"Documents à joindre à votre demande"}
          />
        </section>

        <div className="fr-grid-row fr-grid-row--gutters">
          <div className="fr-col-12">
            <p>
              Afin de pouvoir valider votre demande, vous devez fournir les
              documents relatifs à votre situation.
            </p>

            <ButtonsGroup
              inlineLayoutWhen="always"
              alignment="right"
              buttonsIconPosition="right"
              buttonsSize="small"
              buttons={[
                {
                  priority: "secondary",
                  iconId: "fr-icon-add-line",

                  children: "Ajouter des documents",
                  nativeButtonProps: {
                    type: "button",
                  },
                  onClick: () => {
                    refModaleAjoutPieceJointe.current?.ouvrir();
                  },
                },
              ]}
            />
          </div>
        </div>

        <div id="" className="fr-grid-row fr-grid-row--gutters">
          <div className="container fr-col-12 fr-col-lg-3">
            <SideMenu
              align="left"
              burgerMenuButtonText="Mes documents"
              title="Mes documents"
              items={Object.values(TypePieceJointe.liste).map(
                (type: TypePieceJointe) => ({
                  linkProps: {
                    href: `#type-piece—jointe-${type.type}`,
                  },
                  isActive: type.equals(typePieceJointe),
                  text: (
                    <span
                      className="fr-text--sm fr-text--regular"
                      onClick={() => {
                        if (!dossier.compterPiecesJointesDeType(type)) {
                          setTypePieceJointe(type);
                          setPieceJointe(undefined);
                        }
                      }}
                    >
                      {type.libelle}
                      <>
                        <Badge
                          severity={
                            dossier.compterPiecesJointesDeType(type) > 0
                              ? "success"
                              : "error"
                          }
                          as="span"
                          noIcon={true}
                          className="fr-ml-1v"
                        >
                          {dossier.compterPiecesJointesDeType(type)}
                        </Badge>
                      </>
                    </span>
                  ),
                  ...(dossier.compterPiecesJointesDeType(type)
                    ? {
                        items: dossier
                          .getPiecesJointesDeType(type)
                          .map((pj) => ({
                            text: (
                              <span
                                onClick={() => {
                                  setPieceJointe(pj);
                                  setTypePieceJointe(pj.type);
                                }}
                              >
                                {pj.nom}
                              </span>
                            ),
                            isActive: pieceJointe?.id === pj.id,
                            linkProps: {
                              href: `#piece—jointe-${pj.id}`,
                            },
                          })),
                      }
                    : {}),
                }),
              )}
            />
          </div>

          <div className="fr-col-12 fr-col-lg-9">
            {!!pieceJointe ? (
              <div
                id={`piece—jointe-${pieceJointe.id}`}
                className="fr-grid-row"
              >
                <Download
                  className="fr-col-12"
                  details={`${pieceJointe.infoFichier}`}
                  label={pieceJointe.nom}
                  linkProps={{
                    href: `${pieceJointe.url}?download`,
                  }}
                />

                {pieceJointe.estPDF() ? (
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
                    alt={pieceJointe.nom}
                    style={{
                      width: "100%",
                      maxHeight: "100vh",
                      objectFit: "contain",
                    }}
                  />
                )}
              </div>
            ) : (
              <div
                id={`type-piece—jointe-${typePieceJointe.type}`}
                className="fr-my-5w fr-mt-md-12w fr-mb-md-10w"
              >
                <div className="fr-col-12  fr-grid-row fr-grid-row--gutters fr-grid-row--middle fr-grid-row--center">
                  <div className="fr-py-0 fr-col-12 fr-col-md-6">
                    <h3>{typePieceJointe.libelle}</h3>
                    <p className="fr-text--sm fr-mb-3w">
                      Votre dossier ne contient toujours pas de{" "}
                      {typePieceJointe.libelle}.
                    </p>

                    <p className="fr-text--lead fr-mb-3w">
                      Au moins un document, photo ou fichier PDF, est requis
                      pour mener l'instruction de votre demande d'indemnisation.
                    </p>
                    <p className="fr-text--sm fr-mb-5w">
                      Vous pouvez téléverser des documents dès à présent depuis
                      la boîte de dialogue qui s'ouvrira en cliquant sur le
                      bouton ci-dessous :
                    </p>
                  </div>
                  <div className="fr-col-12 fr-col-md-3 fr-col-offset-md-1 fr-px-6w fr-px-md-0 fr-py-0">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      className="fr-responsive-img fr-artwork"
                      aria-hidden="true"
                      width="160"
                      height="200"
                      viewBox="0 0 160 200"
                      data-fr-js-ratio="true"
                    >
                      <use
                        className="fr-artwork-motif"
                        href={`${artworkOvoid}#artwork-motif`}
                      ></use>
                      <use
                        className="fr-artwork-background"
                        href={`${artworkOvoid}#artwork-background`}
                      ></use>
                      <g transform="translate(40, 60)">
                        <use
                          className="fr-artwork-decorative"
                          href={`${artworkDocumentAddUrl}#artwork-decorative`}
                        ></use>
                        <use
                          className="fr-artwork-minor"
                          href={`${artworkDocumentAddUrl}#artwork-minor`}
                        ></use>
                        <use
                          className="fr-artwork-major"
                          href={`${artworkDocumentAddUrl}#artwork-major`}
                        ></use>
                      </g>
                    </svg>
                  </div>
                </div>
                <div className="fr-col-12 fr-grid-row fr-grid-row--right">
                  <Button
                    priority="secondary"
                    type="button"
                    size="small"
                    iconId="fr-icon-add-line"
                    iconPosition="right"
                    onClick={() => refModaleAjoutPieceJointe.current?.ouvrir()}
                  >
                    Ajouter un(e) {typePieceJointe.libelle}
                  </Button>
                </div>
              </div>
            )}
          </div>
        </div>

        <div className="fr-grid-row fr-grid-row--gutters">
          <div className="fr-col-12">
            <Alert
              className="fr-col-12"
              severity="error"
              title="Des documents sont manquants"
              closable={true}
              description={
                <>
                  <p>Il manque les documents suivants :</p>

                  <ul>
                    <li>Attestation complétée par les forces de l'ordre</li>
                    <li>
                      Facture acquittée attestant de la réalité des travaux de
                      remise en état à l'identique
                    </li>
                  </ul>
                </>
              }
            />
          </div>
        </div>

        <ButtonsGroup
          inlineLayoutWhen="always"
          alignment="right"
          buttonsIconPosition="right"
          buttons={[
            {
              priority: "secondary",
              children: "Revenir à l'étape précédente",
              nativeButtonProps: {
                type: "button",
              },
              onClick: () =>
                naviguer({
                  from: Route.fullPath,
                  to: "../2-infos-requerant",
                  search: {} as any,
                }),
            },
            {
              priority: "primary",
              children: "Soumettre ma demande",
              nativeButtonProps: {
                type: "submit",
                role: "submit",
              },
              onClick: async () => {
                await dossierManager.soumettre(reference);
                await naviguer({
                  to: "/requerant/mes-demandes",
                  search: {} as any,
                });
              },
            },
          ]}
        />
      </form>
    </>
  );
}
