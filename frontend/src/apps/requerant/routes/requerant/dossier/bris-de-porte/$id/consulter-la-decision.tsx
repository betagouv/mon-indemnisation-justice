import { container } from "@/apps/requerant/container.ts";
import { AfficherPieceJointe } from "@/apps/requerant/dossier/components/PieceJointe/AfficherPieceJointe.tsx";
import { Dossier, PieceJointe } from "@/apps/requerant/models";
import { RouteurRequerant } from "@/apps/requerant/routeur";
import { DossierManagerInterface } from "@/apps/requerant/services/DossierManager.ts";
import { Loader } from "@/common/composants/Loader.tsx";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { FrIconClassName } from "@codegouvfr/react-dsfr/fr/generatedFromCss/classNames";
import Tabs from "@codegouvfr/react-dsfr/Tabs";
import { createFileRoute, notFound, useRouter } from "@tanstack/react-router";
import { useInjection } from "inversify-react";
import React from "react";
import { SignatureManuelleModale } from "@/apps/requerant/composants/signature/SignatureManuelleModale.tsx";
import { SignatureElectroniqueModale } from "@/apps/requerant/composants/signature/SignatureElectroniqueModale.tsx";

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

  return (
    <div className="fr-container">
      <div className="fr-grid-row">
        <div className="fr-col-12">
          <h1>Votre demande d'indemnisation</h1>

          {dossier.estAccepte ? (
            <>
              <p>
                La demande d’indemnisation {dossier.reference} a obtenu une
                décision favorable ci-jointe.
              </p>
              <p>
                Le{" "}
                <span className="fr-text--bold">
                  montant de l’indemnisation proposé
                </span>
                est de{" "}
                <span className="fr-text--bold">
                  {(dossier.montantIndemnisation as number).toLocaleString()}
                </span>
                . Pour{" "}
                <span className="fr-text--bold">accepter la proposition</span>{" "}
                et
                <span className="fr-text--bold">déclencher le versement</span>,
                veuillez cliquer sur le bouton{" "}
                <span className="fr-text--bold">
                  « Accepter la proposition »
                </span>
                , ensuite{" "}
                <span className="fr-text--bold">signer et téléverser</span> la
                déclaration d’acceptation ci-jointe en suivant les instructions.
              </p>
            </>
          ) : (
            <p>
              La demande d’indemnisation {dossier.reference} a fait l’objet
              d’une décision défavorable. La motivation de notre refus est
              précisée dans le courrier de rejet ci-joint.
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
          ) : dossier.estEligibleSignatureElectronique ? (
            <SignatureElectroniqueModale
              dossier={dossier}
              onDemarrer={async (contexte) => {
                await dossierManager.demarrerSignature(dossier.id, contexte);
                await routeur.invalidate();
              }}
            />
          ) : (
            <SignatureManuelleModale
              dossier={dossier}
              onSigne={async (fichierSigne: File) => {
                await dossierManager.accepter(dossier.id, fichierSigne);
                await routeur.invalidate();
              }}
            />
          ))}

        <div className="fr-col-12">
          <Tabs
            tabs={[
              {
                label: dossier.estAccepte
                  ? "Proposition d'indemnisation"
                  : "Courrier de rejet",
                iconId: "fr-icon-checkbox-circle-line",
                isDefault: true,
                content: dossier.getCourrierDecision() && (
                  <AfficherPieceJointe
                    pieceJointe={dossier.getCourrierDecision() as PieceJointe}
                    telecharger={false}
                  />
                ),
              },
              ...(dossier.estAccepte &&
              !dossier.estEligibleSignatureElectronique
                ? [
                    {
                      label: "Déclaration d'acceptation",
                      isDefault: false,
                      iconId: "fr-icon-chat-check-line" as FrIconClassName,
                      content:
                        dossier.getFormulaireDeclarationAcceptation() && (
                          <AfficherPieceJointe
                            pieceJointe={
                              dossier.getDeclarationAcceptation() ??
                              (dossier.getFormulaireDeclarationAcceptation() as PieceJointe)
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
