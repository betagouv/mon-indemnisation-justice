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
import React, { useEffect, useMemo, useState } from "react";
import { SignatureManuelleModale } from "@/apps/requerant/composants/signature/SignatureManuelleModale.tsx";
import { SignatureElectroniqueModale } from "@/apps/requerant/composants/signature/SignatureElectroniqueModale.tsx";
import {
  DeclarationAcceptation,
  estDeclarationAConsulter,
  etatDeclarationAcceptation,
} from "@/apps/requerant/composants/signature/DeclarationAcceptation.tsx";
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

type Onglet = "decision" | "declaration";

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

  // Recevoir le dossier à jour dès qu'il change (ex : signature électronique validée). Le cookie d'abonnement est
  // posé par l'API au chargement du dossier (cf. `DiffuseurDossierRequerant`)
  useAbonnementMercure<object>(`requerant/dossier/${dossier.id}`, (donnees) => {
    setDossier(plainToInstance(Dossier, donnees));
  });

  const etatDeclaration = useMemo(
    () => etatDeclarationAcceptation(dossier),
    [dossier],
  );

  // Onglet sélectionné, mémorisé pour survivre aux mises à jour du dossier (Mercure, rechargement par le routeur).
  // Au chargement, la déclaration est affichée si une signature électronique est en cours, ou fermée et à recommencer
  const [ongletSelectionne, setOngletSelectionne] = useState<Onglet>(() =>
    estDeclarationAConsulter(etatDeclarationAcceptation(dossierCharge))
      ? "declaration"
      : "decision",
  );

  // L'onglet de la déclaration peut ne pas (ou plus) exister : on se rabat alors sur celui de la décision
  const onglet: Onglet =
    etatDeclaration === "absente" ? "decision" : ongletSelectionne;

  const onglets = useMemo(
    () => [
      {
        tabId: "decision",
        label: dossier.estAccepte
          ? "Proposition d'indemnisation"
          : "Courrier de rejet",
        iconId: "fr-icon-checkbox-circle-line" as FrIconClassName,
      },
      ...(etatDeclaration !== "absente"
        ? [
            {
              tabId: "declaration",
              label: "Déclaration d'acceptation",
              iconId: "fr-icon-chat-check-line" as FrIconClassName,
            },
          ]
        : []),
    ],
    [dossier.estAccepte, etatDeclaration],
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
                , pour ensuite{" "}
                <span className="fr-text--bold">
                  signer
                  {!dossier.estEligibleSignatureElectronique && (
                    <> et téléverser</>
                  )}
                </span>{" "}
                la déclaration d’acceptation ci-jointe en suivant les
                instructions.
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
                // Afficher la signature électronique (ou sa préparation) dès son démarrage
                setOngletSelectionne("declaration");
              }}
            />
          ) : (
            <SignatureManuelleModale
              dossier={dossier}
              onSigne={async (fichierSigne: File) => {
                await dossierManager.accepter(dossier.id, fichierSigne);
                await routeur.invalidate();
                // Afficher la déclaration signée qui vient d'être déposée
                setOngletSelectionne("declaration");
              }}
            />
          ))}

        <div className="fr-col-12">
          <Tabs
            tabs={onglets}
            selectedTabId={onglet}
            onTabChange={(tabId) => setOngletSelectionne(tabId as Onglet)}
          >
            {onglet === "declaration" ? (
              <DeclarationAcceptation
                dossier={dossier}
                etat={etatDeclaration}
              />
            ) : (
              dossier.getCourrierDecision() && (
                <AfficherPieceJointe
                  pieceJointe={dossier.getCourrierDecision() as PieceJointe}
                  telecharger={false}
                />
              )
            )}
          </Tabs>
        </div>
      </div>
    </div>
  );
}
