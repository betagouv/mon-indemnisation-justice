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
import React, { useEffect, useState } from "react";
import { SignatureManuelleModale } from "@/apps/requerant/composants/signature/SignatureManuelleModale.tsx";
import { SignatureElectroniqueModale } from "@/apps/requerant/composants/signature/SignatureElectroniqueModale.tsx";
import { convertirEnEuros } from "@common/services/devise.ts";
import { useAbonnementMercure } from "@common/hooks/useAbonnementMercure.ts";
import { plainToInstance } from "class-transformer";

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
  const { dossier: dossierCharge }: { dossier: Dossier } =
    Route.useLoaderData();

  // Le dossier affiché, rechargé par le loader ou mis à jour en direct par Mercure
  const [dossier, setDossier] = useState<Dossier>(dossierCharge);
  useEffect(() => setDossier(dossierCharge), [dossierCharge]);

  // Tant que la signature électronique est en cours, être notifié du dossier à jour dès qu'elle est validée
  useAbonnementMercure<object>(
    dossier.requeteSignature && !dossier.estAccepteRequerant
      ? `requerant/signature/${dossier.requeteSignature.id}`
      : null,
    (donnees) => {
      console.log("données reçues", donnees);
      setDossier(plainToInstance(Dossier, donnees));
    },
  );

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
                &nbsp;est de{" "}
                <span className="fr-text--bold">
                  {convertirEnEuros(dossier.montantIndemnisation as number)}
                </span>
                . Pour{" "}
                <span className="fr-text--bold">accepter la proposition</span>{" "}
                et
                <span className="fr-text--bold">
                  &nbsp;déclencher le versement
                </span>
                , veuillez cliquer sur le bouton{" "}
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
            key={`signature-${dossier.requeteSignature?.id ? "electronique-" + dossier.requeteSignature?.id : "manuelle"}-${dossier.estAccepteRequerant ? "ok" : "en-cours"}`}
            tabs={[
              {
                label: dossier.estAccepte
                  ? "Proposition d'indemnisation"
                  : "Courrier de rejet",
                iconId: "fr-icon-checkbox-circle-line",
                isDefault:
                  !dossier.estEligibleSignatureElectronique ||
                  !dossier.requeteSignature,
                content: dossier.getCourrierDecision() && (
                  <AfficherPieceJointe
                    pieceJointe={dossier.getCourrierDecision() as PieceJointe}
                    telecharger={false}
                  />
                ),
              },
              ...(dossier.estAccepte &&
              (!dossier.estEligibleSignatureElectronique ||
                dossier.requeteSignature)
                ? [
                    {
                      label: "Déclaration d'acceptation",
                      isDefault:
                        dossier.estEligibleSignatureElectronique &&
                        !!dossier.requeteSignature,
                      iconId: "fr-icon-chat-check-line" as FrIconClassName,
                      content:
                        dossier.estEligibleSignatureElectronique &&
                        !dossier.estAccepteRequerant ? (
                          <iframe
                            allow="geolocation"
                            src={dossier.requeteSignature?.urlExterne}
                            title="Signature électronique de la déclaration d'acceptation"
                            style={{
                              display: "block",
                              width: "100%",
                              height: "85vh",
                              minHeight: "600px",
                              border: "none",
                            }}
                          />
                        ) : (
                          (dossier.getDeclarationAcceptation() ??
                            dossier.getFormulaireDeclarationAcceptation()) && (
                            <AfficherPieceJointe
                              pieceJointe={
                                (dossier.getDeclarationAcceptation() ??
                                  dossier.getFormulaireDeclarationAcceptation()) as PieceJointe
                              }
                              telecharger={false}
                            />
                          )
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
