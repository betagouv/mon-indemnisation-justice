import React from "react";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import "./liste/dossier-liste-element.css";

interface DossierATransmettre {
  readonly id: number;
  readonly reference: string;
  readonly requerant: string;
  readonly montantIndemnisation: number;
  readonly dateValidation: Date;
  readonly agentValidateur: string;
}

const formateurMontantEuro = new Intl.NumberFormat("fr-FR", {
  style: "currency",
  currency: "EUR",
  trailingZeroDisplay: "auto",
  minimumFractionDigits: 2,
  maximumFractionDigits: 2,
});

function DossierListeElementLigne({
  dossier,
}: {
  dossier: DossierATransmettre;
}) {
  return (
    <div className="fr-grid-row mij-dossier-liste-element">
      <div className="fr-col-3">
        <strong className="fr-text--bold fr-text--lg">
          {dossier.reference}
        </strong>
      </div>

      <div className="fr-col-7 mij-dossier-details">
        <ul>
          <li>{dossier.requerant}</li>
          <li>{formateurMontantEuro.format(dossier.montantIndemnisation)}</li>
          <li>validé le 15 août par {dossier.agentValidateur}</li>
        </ul>
      </div>

      <div className="fr-col-2">
        <ButtonsGroup
          inlineLayoutWhen="always"
          alignment="right"
          buttonsIconPosition="left"
          buttonsEquisized={false}
          buttonsSize="small"
          buttons={[
            {
              size: "small",
              priority: "tertiary no outline",
              iconId: "fr-icon-download-line",
              children: "Télécharger",
              className: "fr-mb-0",
            },
            {
              size: "small",
              priority: "tertiary no outline",
              iconId: "fr-icon-eye-line",
              children: "Voir",
              className: "fr-mb-0",
            },
          ]}
        />
      </div>
    </div>
  );
}

export function ListeDossierATransmettre() {
  const dossiers: DossierATransmettre[] = [
    {
      id: 56,
      reference: "BRI/20250123/001",
      requerant: "Mr Renaud VOITURE",
      montantIndemnisation: 1732.56,
      dateValidation: new Date("2025-05-15"),
      agentValidateur: "Walid HATEUR",
    } as DossierATransmettre,
    {
      id: 87,
      reference: "BRI/20250217/002",
      requerant: "Mme Ariane FUSEE",
      montantIndemnisation: 2101.8,
      dateValidation: new Date("2025-05-17"),
      agentValidateur: "Walid HATEUR",
    } as DossierATransmettre,
  ];

  return (
    <>
      <h1>Dossiers à transmettre au bureau du budget</h1>

      <h4>
        {dossiers.length} dossier{dossiers.length > 1 ? "s" : ""}
      </h4>

      <div>
        {dossiers.map((dossier: DossierATransmettre) => (
          <DossierListeElementLigne key={dossier.id} dossier={dossier} />
        ))}
      </div>
    </>
  );
}
