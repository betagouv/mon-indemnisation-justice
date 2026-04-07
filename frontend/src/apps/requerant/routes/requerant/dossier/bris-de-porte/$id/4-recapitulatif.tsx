import { NonTrouveComposant } from "@/apps/requerant/composants/routeur/NonTrouveComposant.tsx";
import { container } from "@/apps/requerant/container.ts";
import { Dossier } from "@/apps/requerant/models";
import { DossierManagerInterface } from "@/apps/requerant/services/DossierManager.ts";
import { Loader } from "@/common/composants/Loader.tsx";
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
import { default as React, useEffect, useState } from "react";

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

  // Récupération du routeur, uniquement pour pouvoir invalider son cache
  const routeur = useRouter();

  const [sauvegardeEnCours, setSauvegardeEnCours] = useState<boolean>(false);

  useEffect(() => {
    // Invalidate data on component mount to ensure fresh data for summary
    routeur.invalidate();
  }, [routeur]);

  const handleSubmission = async () => {
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
  };

  return (
    <div className="container">
      <h1 className="fr-mb-4">Récapitulatif de votre demande</h1>

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
            <section className="fr-card fr-mb-5w p-4">
              <h3 className="fr-mb-3w">Informations générales (Steps 1 & 2)</h3>
              <p>
                Ici, vous devriez afficher un résumé des informations collectées
                aux étapes précédentes :
                {/* Replace this with components displaying Requérant info, etc. */}
                <ul className="list-group mt-3">
                  <li className="list-group-item">
                    <strong>Demandeur:</strong>{" "}
                    {dossier.personnePhysique?.personne.prenom ||
                      "Non renseigné"}
                  </li>
                  <li className="list-group-item">
                    <strong>Objet:</strong> "Non renseigné"
                  </li>
                </ul>
              </p>
            </section>

            {/* Summary for Step 3 */}
            <section className="fr-card fr-mb-5w p-4 bg-light">
              <h3 className="fr-mb-3w">Pièces Jointes (Step 3)</h3>
              <p>
                Votre dossier contient {dossier.piecesJointes.length} pièce(s)
                jointe(s).
              </p>
              {dossier.piecesJointes.length > 0 ? (
                <ul className="list-group">
                  {dossier.piecesJointes.map((pj) => (
                    <li
                      key={pj.id}
                      className="list-group-item d-flex justify-content-between align-items-center"
                    >
                      {pj.nom} (Type: {pj.type?.type || "Inconnu"})
                      <a
                        href={`${pj.url}?download`}
                        className="btn btn-sm btn-outline-primary"
                      >
                        Télécharger
                      </a>
                    </li>
                  ))}
                </ul>
              ) : (
                <p>Aucune pièce jointe n'a été ajoutée à ce stade.</p>
              )}
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
                : "Soumettre définitivement ma demande",
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
