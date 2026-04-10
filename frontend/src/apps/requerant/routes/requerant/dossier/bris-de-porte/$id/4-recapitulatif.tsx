import { PieceJointePanelNavigation } from "@/apps/requerant/composants/piecesJointes/PieceJointePanelNavigation";
import { NonTrouveComposant } from "@/apps/requerant/composants/routeur/NonTrouveComposant.tsx";
import { container } from "@/apps/requerant/container.ts";
import {
  Dossier,
  getRapportAuLogementLibelle,
  PieceJointe,
} from "@/apps/requerant/models";
import { libellerNomTypePersonneMorale } from "@/apps/requerant/models/TypePersonneMoraleType";
import { DossierManagerInterface } from "@/apps/requerant/services/DossierManager.ts";
import { Loader } from "@/common/composants/Loader.tsx";
import { dateSimple } from "@/common/services/date";
import { fr, FrCxArg } from "@codegouvfr/react-dsfr";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import {
  createFileRoute,
  notFound,
  NotFoundRouteProps,
  useNavigate,
  useRouter,
} from "@tanstack/react-router";
import { useInjection } from "inversify-react";
import { default as React, useEffect, useMemo, useState } from "react";

export const Route = createFileRoute(
  "/requerant/dossier/bris-de-porte/$id/4-recapitulatif",
)({
  component: Etape4Recapitulatif,
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

function Etape4Recapitulatif() {
  // Action de navigation
  const naviguer = useNavigate({
    from: Route.fullPath,
  });

  // Récupération du DossierManager, en charge des opérations sur le dossier
  const dossierManager = useInjection<DossierManagerInterface>(
    DossierManagerInterface.$,
  );

  // Récupérer les données chargées (Dossier)
  const { dossier }: { dossier: Dossier } = Route.useLoaderData();

  const e = useMemo(
    () => (dossier.getRequerantPersonne()?.estFeminin() ? "e" : ""),
    [dossier.getRequerantPersonne()],
  );

  // Récupération du routeur, uniquement pour pouvoir invalider son cache
  const routeur = useRouter();

  const [afficherPiecesJointes, setAfficherPiecesJointes] =
    useState<boolean>(false);

  // La pièce jointe sélectionnée pour être visualisée.
  const [pieceJointeSelectionnee, selectionnerPieceJointe] =
    useState<PieceJointe>(dossier.piecesJointes.at(0) as PieceJointe);

  const nomPieceJointePrecedente = (
    dossier.piecesJointes.at(
      dossier.piecesJointes.indexOf(pieceJointeSelectionnee) - 1,
    ) as PieceJointe
  ).nom;
  const selectionnerPieceJointePrecedente = () => {
    selectionnerPieceJointe(
      dossier.piecesJointes.at(
        dossier.piecesJointes.indexOf(pieceJointeSelectionnee) - 1,
      ) as PieceJointe,
    );
  };

  const selectionnerPieceJointeSuivante = () => {
    selectionnerPieceJointe(
      dossier.piecesJointes.at(
        (dossier.piecesJointes.indexOf(pieceJointeSelectionnee) + 1) %
          dossier.piecesJointes.length,
      ) as PieceJointe,
    );
  };

  const nomPieceJointeSuivante = (
    dossier.piecesJointes.at(
      (dossier.piecesJointes.indexOf(pieceJointeSelectionnee) + 1) %
        dossier.piecesJointes.length,
    ) as PieceJointe
  ).nom;

  const [sauvegardeEnCours, setSauvegardeEnCours] = useState<boolean>(false);

  useEffect(() => {
    // Invalidate data on component mount to ensure fresh data for summary
    routeur.invalidate();
  }, [routeur]);

  const handleSubmission = async () => {
    if (dossier.estBrouillon) {
      setSauvegardeEnCours(true);
      try {
        setSauvegardeEnCours(true);
        const dossierDepose = await dossierManager.soumettre(dossier.id);
        setSauvegardeEnCours(false);

        await naviguer({
          to: "/requerant/mes-demandes",
          search: true,
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
      } catch (error) {
        console.error("Error submitting dossier:", error);
        // Handle submission error UI
      } finally {
        setSauvegardeEnCours(false);
      }
    } else {
      await naviguer({
        to: "/requerant/mes-demandes",
        search: true,
      });
    }
  };

  return (
    <div className="container">
      <h1>Déclarer un bris de porte</h1>

      <div className="row mb-5">
        <div className="col-12">
          <Stepper
            currentStep={4}
            stepCount={4}
            title={"Récapitulatif de la demande"}
          />
        </div>
      </div>

      <div className="row">
        <div className="col-12">
          <div className="fr-col-12">
            <section className="fr-card fr-mb-5w fr-p-5w">
              <h4 className="fr-mb-3w">
                Informations concernant le bris de porte
              </h4>
              <p>
                Vous signalez le bris de la porte
                {dossier.estPorteBlindee ? " blindée" : ""}, par les forces de
                l'ordre, du logement sis {dossier.adresse.libelle}
                {dossier.rapportAuLogement !== "AUTRE" && (
                  <>
                    , dont vous êtes{" "}
                    {getRapportAuLogementLibelle(
                      dossier.rapportAuLogement,
                    ).toLowerCase()}
                  </>
                )}
                , en date du {dateSimple(dossier.dateOperation)}.
              </p>

              {dossier.description && (
                <>
                  <p>Vous nous avez fourni la description suivante :</p>
                  <blockquote className="fr-citation">
                    {dossier.description}
                  </blockquote>
                </>
              )}

              <ButtonsGroup
                inlineLayoutWhen="always"
                alignment="right"
                buttonsIconPosition="right"
                buttonsSize="medium"
                buttons={[
                  {
                    priority: "secondary",
                    children: "Modifier",
                    iconId: "fr-icon-pencil-line",
                  },
                ]}
              />
            </section>

            <section className="fr-card fr-mb-5w fr-p-5w">
              <h4 className="fr-mb-3w">Vos données personnelles</h4>
              {dossier.personneMorale ? (
                <>
                  <p>
                    Vous faites la demande d'indemnisation en tant que personne
                    morale pour{" "}
                    {libellerNomTypePersonneMorale(
                      dossier.personneMorale.typePersonneMorale,
                      { defini: true },
                    )}{" "}
                    {dossier.personneMorale.raisonSociale}, numéro{" "}
                    {dossier.personneMorale.siren.length === 9
                      ? "SIRET"
                      : "SIREN"}{" "}
                    dossier.personneMorale.siren.
                  </p>

                  <p>
                    Vous, {dossier.personneMorale.representantLegal.libelle()},
                    attestez en être{" "}
                    {dossier.getRequerantPersonne()?.estFeminin() ? "la" : "le"}{" "}
                    représentant{e} légal{e} ou disposer d'un mandat de
                    signature.
                  </p>
                </>
              ) : (
                <p>
                  Vous, {dossier.personnePhysique?.personne.libelle()},
                  domicilié{e} au {dossier.personnePhysique?.adresse.libelle} ,
                  faites la demande d'indemnisation en tant que personne
                  physique.
                </p>
              )}

              <p>
                Vous êtes joignable par téléphone au{" "}
                {dossier.getRequerantPersonne()?.telephone} et par courriel à
                l'adresse{" "}
                <a href={`mailto:${dossier.getRequerantPersonne()?.courriel}`}>
                  {dossier.getRequerantPersonne()?.courriel}
                </a>
              </p>

              <ButtonsGroup
                inlineLayoutWhen="always"
                alignment="right"
                buttonsIconPosition="right"
                buttonsSize="medium"
                buttons={[
                  {
                    priority: "secondary",
                    children: "Modifier",
                    iconId: "fr-icon-pencil-line",
                  },
                ]}
              />
            </section>

            <section className="fr-card fr-mb-5w fr-p-5w">
              <h4 className="fr-mb-3w">Pièces jointes</h4>
              <p>Vous joignez à votre demande les pièces jointes suivantes :</p>

              <div className="fr-grid-row fr-grid-row--gutters">
                {/* Panel de navigation */}
                <PieceJointePanelNavigation
                  pieceJointe={pieceJointeSelectionnee}
                  nomPieceJointePrecedente={nomPieceJointePrecedente}
                  onSelectionPieceJointePrecedente={() =>
                    selectionnerPieceJointePrecedente()
                  }
                  nomPieceJointeSuivante={nomPieceJointeSuivante}
                  onSelectionPieceJointeSuivante={() =>
                    selectionnerPieceJointeSuivante()
                  }
                />

                <ButtonsGroup
                  className="fr-col-12"
                  inlineLayoutWhen="always"
                  alignment="right"
                  buttonsIconPosition="right"
                  buttonsSize="medium"
                  buttons={[
                    {
                      priority: "tertiary no outline",
                      children: afficherPiecesJointes
                        ? "Masquer les documents"
                        : "Afficher les documents",
                      iconId: afficherPiecesJointes
                        ? "fr-icon-eye-off-line"
                        : "fr-icon-eye-line",
                      onClick: () => {
                        setAfficherPiecesJointes(!afficherPiecesJointes);
                      },
                    },
                    {
                      priority: "secondary",
                      children: "Modifier",
                      iconId: "fr-icon-pencil-line",
                    },
                  ]}
                />

                {/* Toutes les visualisations de pièces jointes sont intégrées au DOM, mais cachée si pas actives afin
                  d'anticiper le chargement des images / PDFs */}
                {dossier.piecesJointes.map((pieceJointe: PieceJointe) => (
                  <div
                    key={`piece—jointe-${pieceJointe.id}`}
                    id={`piece—jointe-${pieceJointe.id}`}
                    className={fr.cx([
                      "fr-col-12",
                      ...(afficherPiecesJointes &&
                      pieceJointeSelectionnee?.id === pieceJointe.id
                        ? []
                        : ["fr-hidden" as FrCxArg]),
                    ])}
                  >
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
              </div>
            </section>
          </div>
        </div>
      </div>

      <div className="fr-mt-5w pt-5 border-top">
        <ButtonsGroup
          inlineLayoutWhen="always"
          alignment="right"
          buttonsIconPosition="right"
          buttonsSize="large"
          buttons={[
            {
              priority: "secondary",
              children: "Retour à la modification",
              disabled: sauvegardeEnCours,
              nativeButtonProps: {
                type: "button",
              },
              onClick: () =>
                naviguer({
                  from: Route.fullPath,
                  to: "/requerant/dossier/bris-de-porte/${dossier.id}/3-pieces-jointes",
                  search: {} as any,
                }),
            },
            {
              priority: "primary",
              children: sauvegardeEnCours
                ? "Soumission en cours..."
                : dossier.estBrouillon
                  ? "Soumettre ma demande"
                  : "Modifier ma demande",
              disabled: sauvegardeEnCours,
              nativeButtonProps: {
                type: "button",
                role: "button",
              },
              onClick: handleSubmission,
            },
          ]}
        />
      </div>
    </div>
  );
}
