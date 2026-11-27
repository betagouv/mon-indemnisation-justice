import { AfficherPieceJointe } from "@/apps/requerant/dossier/components/PieceJointe/AfficherPieceJointe.tsx";
import { Dossier } from "@/apps/requerant/models";
import { Loader } from "@/common/composants/Loader.tsx";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import React from "react";

/**
 * État de la déclaration d'acceptation :
 *
 * - `absente` : rien à montrer, l'onglet est masqué
 * - `en-preparation` : signature électronique créée, mais pas encore activée du côté du prestataire
 * - `a-signer` : signature électronique active et à signer par le requérant
 * - `signee` : déclaration signée, manuellement ou électroniquement
 * - `expiree` : signature électronique expirée, qui peut être recommencée
 *
 * Une signature annulée (par un agent) est ignorée : pour le requérant, tout se passe comme s'il n'y en avait jamais eu.
 */
export type EtatDeclarationAcceptation =
  "absente" | "en-preparation" | "a-signer" | "signee" | "expiree";

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

  // Une signature annulée l'a été par un agent : le requérant repart de zéro
  if (!requeteSignature || requeteSignature.estAnnulee) {
    return "absente";
  }

  if (requeteSignature.estSignee) {
    return "signee";
  }

  if (requeteSignature.estExpiree) {
    return "expiree";
  }

  if (!requeteSignature.estActive) {
    return "en-preparation";
  }

  return "a-signer";
}

/**
 * Signature électronique démarrée, mais pas encore signée par le requérant.
 */
export const estSignatureEnCours = (etat: EtatDeclarationAcceptation) =>
  etat === "en-preparation" || etat === "a-signer";

/**
 * Le requérant doit consulter l'onglet de la déclaration : signature en cours, ou expirée et à recommencer.
 */
export const estDeclarationAConsulter = (etat: EtatDeclarationAcceptation) =>
  estSignatureEnCours(etat) || etat === "expiree";

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

    case "expiree":
      return <SignatureExpiree />;

    case "absente":
      return null;
  }
};

const SignatureExpiree = () => (
  <Alert
    className="fr-my-4w"
    severity="warning"
    title="Signature expirée"
    description="Le délai pour signer la déclaration d'acceptation est dépassé. Pour accepter l'indemnisation, cliquez à nouveau sur « Accepter la proposition »."
    closable={false}
  />
);

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
