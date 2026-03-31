import {
  AjouterPiecesJointesModale,
  AjouterPiecesJointesModaleRef,
} from "@/apps/requerant/composants/piecesJointes/AjouterPiecesJointesModale.tsx";
import { NonTrouveComposant } from "@/apps/requerant/composants/routeur/NonTrouveComposant.tsx";
import { container } from "@/apps/requerant/container.ts";
import { Dossier, PieceJointe } from "@/apps/requerant/models";
import {
  PieceJointeType,
  TypePieceJointe,
} from "@/apps/requerant/models/TypePieceJointe.ts";
import {
  DossierManagerInterface,
  NouvellePieceJointe,
} from "@/apps/requerant/services/DossierManager";
import classes from "@/apps/requerant/style/form.module.css";
import { MiseEnAvant } from "@/common/composants/dsfr/MiseEnAvant.tsx";
import { Requis } from "@/common/composants/dsfr/Requis.tsx";
import { Loader } from "@/common/composants/Loader.tsx";
import { fr, FrCxArg } from "@codegouvfr/react-dsfr";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import Badge from "@codegouvfr/react-dsfr/Badge";
import { Button } from "@codegouvfr/react-dsfr/Button";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import Download from "@codegouvfr/react-dsfr/Download";
import artworkDocumentAddUrl from "@codegouvfr/react-dsfr/dsfr/artwork/pictograms/document/document-add.svg?url&no-inline";
import SideMenu from "@codegouvfr/react-dsfr/SideMenu";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { useForm } from "@tanstack/react-form";
import {
  createFileRoute,
  notFound,
  NotFoundRouteProps,
  useNavigate,
  useRouter,
} from "@tanstack/react-router";
import { useInjection } from "inversify-react";
import { default as React, useMemo, useRef, useState } from "react";
import { z } from "zod";

export const Route = createFileRoute(
  "/requerant/dossier/bris-de-porte/$id/3-pieces-jointes",
)({
  component: Etape3PiecesJointes,
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

function Etape3PiecesJointes() {
  // Action de navigation
  const naviguer = useNavigate({
    from: Route.fullPath,
  });

  // Récupération du DossierManager, en charge des opérations sur le dossier
  const dossierManager = useInjection<DossierManagerInterface>(
    DossierManagerInterface.$,
  );

  // Récupérer la référence depuis le paramètre de la route
  const { dossier }: { dossier: Dossier } = Route.useLoaderData();

  // Récupération du routeur, uniquement pour pouvoir invalider son cache
  const routeur = useRouter();

  // La référence vers la modale d'ajout de pièce jointe
  const refModaleAjoutPieceJointe = useRef<AjouterPiecesJointesModaleRef>(null);

  // La pièce jointe sélectionnée pour être visualisée.
  const [pieceJointeSelectionnee, selectionnerPieceJointe] = useState<
    PieceJointe | undefined
  >(dossier.piecesJointes.at(0));

  // Le type de pièce jointe sélectionné pour lister les pièces associées.
  const [typePieceJointeSelectionne, selectionnerTypePieceJointe] =
    useState<TypePieceJointe>(
      pieceJointeSelectionnee?.type ||
        TypePieceJointe.depuisString(
          document.location.hash.replace(/^#type-piece-jointe-/, ""),
        ) ||
        TypePieceJointe.depuis("attestation_information"),
    );

  const [sauvegardeEnCours, setSauvegardeEnCours] = useState<boolean>(false);

  // On génère la liste des types de pièces jointes demandées pour le dossier
  const typesPiecesJointesDemandes = useMemo(
    () =>
      Object.values(TypePieceJointe.liste).filter((type) =>
        type.estDemande(
          dossier.rapportAuLogement,
          dossier.personneMorale?.typePersonneMorale,
          dossier.estLieDeclaration(),
        ),
      ),
    [
      dossier.rapportAuLogement,
      dossier.personneMorale?.typePersonneMorale,
      dossier.estLieDeclaration(),
    ],
  );

  // On génère la liste des types de pièces jointes requises pour le dossier
  const typesPiecesJointesRequis = useMemo(
    () =>
      Object.values(TypePieceJointe.liste).filter((type) =>
        type.estRequis(
          dossier.rapportAuLogement,
          dossier.personneMorale?.typePersonneMorale,
          dossier.estLieDeclaration(),
        ),
      ),
    [
      dossier.rapportAuLogement,
      dossier.personneMorale?.typePersonneMorale,
      dossier.estLieDeclaration(),
    ],
  );

  const estRequis = (type: TypePieceJointe) => {
    return typesPiecesJointesRequis.includes(type);
  };

  // On garde un état du décompte de pièces jointes par type requis ...
  const decomptePiecesJointes: {
    [key in PieceJointeType]?: number;
  } = useMemo(
    () =>
      Object.fromEntries(
        typesPiecesJointesDemandes.map((type) => [
          type.type,
          dossier.compterPiecesJointesDeType(type),
        ]),
      ),
    [dossier.piecesJointes.length, typesPiecesJointesRequis],
  );

  // ... et on propose une fonction de plus haut niveau par dessus
  const decomptePiecesJointesPourType = (type: TypePieceJointe): number => {
    return decomptePiecesJointes[type.type] || 0;
  };

  const formulaire = useForm({
    validators: {
      onSubmit: z.object({
        piecesJointes: z
          .array(z.instanceof(PieceJointe))
          .superRefine((piecesJointes, contexte) => {
            typesPiecesJointesRequis.forEach(
              (typePieceJointeRequis: TypePieceJointe) => {
                if (
                  piecesJointes.filter((pieceJointe: PieceJointe) =>
                    pieceJointe.type.equals(typePieceJointeRequis),
                  ).length == 0
                ) {
                  contexte.addIssue({
                    code: "custom",
                    message: typePieceJointeRequis.type,
                  });
                }
              },
            );
          }),
      }),
    },
    defaultValues: {
      piecesJointes: dossier.piecesJointes,
    },
    listeners: {},
    onSubmit: async ({ formApi }) => {
      if (formApi.state.isValid) {
        if (dossier.estBrouillon) {
          setSauvegardeEnCours(true);
          const dossierDepose = await dossierManager.soumettre(dossier.id);
          setSauvegardeEnCours(false);

          await naviguer({
            to: "/requerant/mes-demandes",
            search: {
              d: dossierDepose.reference,
            } as any,
            state: {
              flash: {
                type: "success",
                titre: "Votre dossier a bien été déposé",
                message: [
                  "Toutes vos informations ont bien été sauvegardées. Un courriel de confirmation vous a été envoyé à l'instant.",
                  `Votre dossier porte la référence ${dossierDepose.reference} et sera traité par un agent du bureau dans les plus brefs délais. Vous serez informés de son évolution par courriel mais pourrez aussi suivre son avancement en vous connectant à votre espace personnel.`,
                ],
              },
            },
          });
        } else {
          // Aucun enregistrement à faire ici, puisque l'étape 3 ne concerne que les pièces jointes qui sont sauvegardées
          // à chaque opération.
          await naviguer({
            to: "/requerant/mes-demandes",
            search: {} as any,
          });
        }
      }
    },
  });

  return (
    <>
      <AjouterPiecesJointesModale
        ref={refModaleAjoutPieceJointe}
        dossier={dossier}
        onComplete={async (
          piecesJointes: NouvellePieceJointe[],
        ): Promise<void> => {
          await dossierManager.ajouterPiecesJointes(dossier.id, piecesJointes);
          formulaire.validate("change");
          // On sait que le dossier a changé, il faut donc invalider le cache du
          // routeur pour le forcer à récupérer le dossier à jour
          await routeur.invalidate();
        }}
        typesPiecesjointes={typesPiecesJointesDemandes}
        title="Ajouter des pièces jointes"
        iconId={"fr-icon-add-line"}
        size="medium"
      />

      <h1>Déclarer un bris de porte</h1>

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
                  disabled: sauvegardeEnCours,
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
              items={typesPiecesJointesDemandes.map(
                (type: TypePieceJointe) => ({
                  linkProps: {
                    href: `#type-piece-jointe-${type.type}`,
                    // Si le dossier n'a aucune pièce jointe de ce type, alors le lien invite vers la section descriptive
                    // du type
                    onClick: () => {
                      if (!decomptePiecesJointesPourType(type)) {
                        selectionnerTypePieceJointe(type);
                        selectionnerPieceJointe(undefined);
                      }
                    },
                  },
                  isActive: type.equals(typePieceJointeSelectionne),
                  text: (
                    <span className="fr-text--sm fr-text--regular">
                      {type.libelle({
                        court: true,
                        pluriel: false,
                        enCapitales: true,
                        dossier: dossier,
                      })}{" "}
                      {estRequis(type) && <Requis />}
                      <>
                        <Badge
                          severity={
                            estRequis(type)
                              ? decomptePiecesJointesPourType(type) > 0
                                ? "success"
                                : "error"
                              : undefined
                          }
                          as="span"
                          noIcon={true}
                          className="fr-ml-1v"
                        >
                          {decomptePiecesJointesPourType(type)}
                        </Badge>
                      </>
                    </span>
                  ),

                  ...(decomptePiecesJointesPourType(type)
                    ? {
                        items: dossier
                          .getPiecesJointesDeType(type)
                          .map((pj) => ({
                            text: <span>{pj.nom}</span>,
                            isActive: pieceJointeSelectionnee?.id === pj.id,
                            linkProps: {
                              href: `#piece-jointe-${pj.id}`,
                              onClick: () => {
                                selectionnerPieceJointe(pj);
                                selectionnerTypePieceJointe(pj.type);
                              },
                            },
                          })),
                      }
                    : {}),
                }),
              )}
            />
          </div>

          <div className="fr-col-12 fr-col-lg-9">
            {/* Toutes les visualisations de pièces jointes sont intégrées au DOM, mais cachée si pas actives afin
             d'anticiper le chargement des images / PDFs */}
            {dossier.piecesJointes.map((pieceJointe: PieceJointe) => (
              <div
                key={`piece—jointe-${pieceJointe.id}`}
                id={`piece—jointe-${pieceJointe.id}`}
                className={fr.cx([
                  "fr-grid-row",
                  ...(pieceJointeSelectionnee?.id === pieceJointe.id
                    ? []
                    : ["fr-hidden" as FrCxArg]),
                ])}
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
            ))}

            {!pieceJointeSelectionnee && (
              <MiseEnAvant
                id={`type-piece—jointe-${typePieceJointeSelectionne.type}`}
                className="fr-my-5w fr-mt-md-12w fr-mb-md-10w"
                action={
                  <div className="fr-col-12 fr-grid-row fr-grid-row--left">
                    <Button
                      priority="primary"
                      type="button"
                      size="small"
                      iconId="fr-icon-add-line"
                      iconPosition="right"
                      disabled={sauvegardeEnCours}
                      onClick={() =>
                        refModaleAjoutPieceJointe.current?.ouvrir(
                          typePieceJointeSelectionne,
                        )
                      }
                    >
                      Ajouter{" "}
                      {typePieceJointeSelectionne.libelle({
                        court: true,
                        pluriel: true,
                        defini: false,
                        dossier: dossier,
                      })}
                    </Button>
                  </div>
                }
                pictogrammeUrl={artworkDocumentAddUrl}
              >
                <h3>
                  {typePieceJointeSelectionne.libelle({
                    court: false,
                    pluriel: false,
                    dossier: dossier,
                    enCapitales: true,
                  })}
                </h3>
                <p className="fr-text--sm fr-mb-3w">
                  Votre dossier ne contient toujours pas{" "}
                  {typePieceJointeSelectionne.libelle({
                    court: true,
                    pluriel: false,
                    dossier: dossier,
                    de: true,
                  })}
                  .
                </p>

                <p className="fr-text--lead fr-mb-3w">
                  Au moins un document, photo ou fichier PDF, est requis pour
                  mener l'instruction de votre demande d'indemnisation.
                </p>
                <p className="fr-text--sm fr-mb-5w">
                  Vous pouvez téléverser des documents dès à présent depuis la
                  boîte de dialogue qui s'ouvrira en cliquant sur le bouton
                  ci-dessous :
                </p>
              </MiseEnAvant>
            )}
          </div>
        </div>

        <formulaire.Subscribe
          selector={(state) => state.fieldMeta.piecesJointes?.errors}
          children={(errors) => {
            const typesPiecesJointesManquantes =
              errors?.map((error) => TypePieceJointe.depuis(error.message)) ??
              [];
            return (
              <>
                {typesPiecesJointesManquantes.length > 0 && (
                  <div className="fr-grid-row fr-grid-row--gutters">
                    <div className="fr-col-12">
                      <Alert
                        className="fr-col-12"
                        severity="error"
                        title="Des documents sont manquants"
                        description={
                          typesPiecesJointesManquantes.length == 1 ? (
                            <p>
                              Il manque au moins{" "}
                              {typesPiecesJointesManquantes.at(0)?.libelle({
                                court: true,
                                pluriel: false,
                                defini: false,
                                dossier: dossier,
                              }) || ""}
                            </p>
                          ) : (
                            <>
                              <p>Il manque les documents suivants :</p>

                              <ul>
                                {typesPiecesJointesManquantes.map(
                                  (typePieceJointeManquante) => (
                                    <li key={typePieceJointeManquante.type}>
                                      {typePieceJointeManquante.libelle({
                                        court: true,
                                        pluriel: false,
                                        dossier: dossier,
                                      })}
                                    </li>
                                  ),
                                )}
                              </ul>
                            </>
                          )
                        }
                      />
                    </div>
                  </div>
                )}
              </>
            );
          }}
        />

        <ButtonsGroup
          inlineLayoutWhen="always"
          alignment="right"
          buttonsIconPosition="right"
          buttons={[
            {
              priority: "secondary",
              children: "Revenir à l'étape précédente",
              disabled: sauvegardeEnCours,
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
              children: sauvegardeEnCours
                ? "Sauvegarde en cours..."
                : dossier.estBrouillon
                  ? "Soumettre ma demande"
                  : "Actualiser ma demande",
              disabled: sauvegardeEnCours,
              nativeButtonProps: {
                type: "submit",
                role: "submit",
              },
            },
          ]}
        />
      </form>
    </>
  );
}
