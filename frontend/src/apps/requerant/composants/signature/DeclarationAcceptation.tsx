import { AfficherPieceJointe } from "@/apps/requerant/dossier/components/PieceJointe/AfficherPieceJointe.tsx";
import { Dossier } from "@/apps/requerant/models";
import { Loader } from "@/common/composants/Loader.tsx";
import React from "react";

/**
 * État de la déclaration d'acceptation :
 *
 * - `absente` : rien à montrer, l'onglet est masqué
 * - `en-preparation` : signature électronique créée, mais pas encore activée du côté du prestataire
 * - `a-signer` : signature électronique active et à signer par le requérant
 * - `signee` : déclaration signée, manuellement ou électroniquement
 */
export type EtatDeclarationAcceptation =
  "absente" | "en-preparation" | "a-signer" | "signee";

export function etatDeclarationAcceptation(
  dossier: Dossier,
): EtatDeclarationAcceptation {
  if (!dossier.estAccepte) {
    return "absente";
  }

  // Signature manuelle : seule la déclaration signée et déposée par le requérant est affichée
  if (!dossier.estEligibleSignatureElectronique) {
    return dossier.getDeclarationAcceptation() ? "signee" : "absente";
  }

  // Signature électronique : la requête est créée à la fin du parcours de la modale « Accepter l'indemnisation »
  const requeteSignature = dossier.requeteSignature;

  if (!requeteSignature) {
    return "absente";
  }

  if (!requeteSignature.estActive) {
    return "en-preparation";
  }

  return requeteSignature.estSignee ? "signee" : "a-signer";
}

/**
 * Signature électronique démarrée, mais pas encore signée par le requérant.
 */
export const estSignatureEnCours = (etat: EtatDeclarationAcceptation) =>
  etat === "en-preparation" || etat === "a-signer";

export const DeclarationAcceptation = ({
  dossier,
  etat,
}: {
  dossier: Dossier;
  etat: EtatDeclarationAcceptation;
}) => {
  switch (etat) {
    case "en-preparation":
      return (
        <DocumentEnPreparation message="Le document à signer est en cours de génération, il s'affichera ici dans quelques instants." />
      );

    case "a-signer":
      return (
        <PageSignatureElectronique
          url={dossier.requeteSignature?.urlExterne as string}
        />
      );

    case "signee": {
      const declaration = dossier.getDeclarationAcceptation();

      // Juste après la signature électronique, le document signé peut ne pas encore être rattaché au dossier
      return declaration ? (
        <AfficherPieceJointe pieceJointe={declaration} telecharger={false} />
      ) : (
        <DocumentEnPreparation message="La déclaration signée est en cours de récupération, elle s'affichera ici dans quelques instants." />
      );
    }

    case "absente":
      return null;
  }
};

const DocumentEnPreparation = ({ message }: { message: string }) => (
  <div className="fr-my-4w" style={{ textAlign: "center" }}>
    <Loader />
    <p className="fr-mt-2w">{message}</p>
  </div>
);

const PageSignatureElectronique = ({ url }: { url: string }) => (
  <iframe
    allow="geolocation"
    src={url}
    title="Signature électronique de la déclaration d'acceptation"
    style={{
      display: "block",
      width: "100%",
      height: "85vh",
      minHeight: "600px",
      border: "none",
    }}
  />
);
