import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import React from "react";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { Document } from "@/apps/requerant/dossier/components/PieceJointe/PieceJointe.tsx";

export const Route = createFileRoute(
  "/requerant/dossier/bris-de-porte/$reference/3-pieces-jointes",
)({
  component: Etape3PiecesJointes,
});

const CategoriesDocuments = {
  attestation_information: "Attestation complétée par les forces de l'ordre",
  photo_prejudice: "Photos de la porte endommagée",
  carte_identite: "Copie de votre pièce d'identité recto-verso",

  facture:
    "Facture acquittée attestant de la réalité des travaux de remise en état à l'identique",
  rib: "Votre relevé d'identité bancaire", // "Relevé d'identité bancaire de votre société" si personne morale
  titre_propriete: "Titre de propriété", // Demandé et requis si est propriétaire ou bailleur, juste demandé si autre
  contrat_location: "Contrat de location", // Demandé et requis si est locataire ou bailleur, juste demandé si autre
  non_prise_en_charge_bailleur:
    "Attestation de non prise en charge par le bailleur", // Demandé et requis si est locataire ou bailleur, juste demandé si autre
  non_prise_en_charge_assurance:
    "Attestation de non prise en charge par l'assurance habitation",
};

function Etape3PiecesJointes() {
  const naviguer = useNavigate({
    from: Route.fullPath,
  });

  return (
    <>
      <h1>Déclarer un bris de porte</h1>

      <section>
        <Stepper
          currentStep={1}
          stepCount={3}
          title={"Données personnelles"}
          nextTitle={"Informations relatives au bris de porte"}
        />
      </section>

      <div className="fr-grid-row fr-grid-row--gutters">
        <div className="fr-col-12">
          {Object.entries(CategoriesDocuments).map(([type, libelle]) => (
            <section className="fr-py-2w" key={type}>
              <Document
                documents={[]}
                libelle={libelle}
                estRequis={true}
                type={type}
                onRemoved={(document) => {
                  console.log(document);
                }}
                onUploaded={(document) => console.log(document)}
              />
            </section>
          ))}
        </div>
      </div>

      <ButtonsGroup
        inlineLayoutWhen="always"
        alignment="right"
        buttonsIconPosition="right"
        buttons={[
          {
            priority: "secondary",
            children: "Revenir à l'étape suivante",
            nativeButtonProps: {
              type: "button",
            },
            onClick: () =>
              naviguer({
                from: Route.fullPath,
                to: "../2-",
                search: {} as any,
              }),
          },
          {
            priority: "primary",
            children: "Valider et passer à l'étape suivante",
            nativeButtonProps: {
              type: "submit",
            },
            onClick: () =>
              naviguer({
                from: Route.fullPath,
                to: "re",
                search: {} as any,
              }),
          },
        ]}
      />
    </>
  );
}
