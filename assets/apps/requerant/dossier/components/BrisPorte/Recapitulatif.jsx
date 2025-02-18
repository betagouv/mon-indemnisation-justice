import React, { useContext } from "react";
import { format as dateFormat } from "date-fns";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { DossierContext } from "@/apps/requerant/dossier/contexts/DossierContext.ts";
import { Document } from "@/apps/requerant/dossier/components/PieceJointe/PieceJointe.jsx";

const Recapitulatif = ({
  gotoFirstSection = null,
  gotoSecondSection = null,
}) => {
  const dossier = useContext(DossierContext);

  return (
    <div className="fr-grid-row fr-grid-row--gutters fr-mb-4w">
      <div className="fr-col-lg-6 fr-col-12">
        <section className="pr-form-section fr-p-4w">
          {dossier.requerant.isPersonneMorale && (
            <>
              <h3>Identité de la société</h3>
              <dl className="fr-mb-2w">
                <strong>
                  {dossier.requerant.personneMorale.raisonSociale}
                </strong>
                <dd>
                  SIREN / SIRET : {dossier.requerant.personneMorale.sirenSiret}
                </dd>
                <dd>{dossier.requerant.adresse.ligne1}</dd>
                <dd>
                  {dossier.requerant.adresse.codePostal}{" "}
                  {dossier.requerant.adresse.localite}
                </dd>
              </dl>

              <p className="fr-mb-5w">
                <label>Représenté.e par</label>
                <br />
                <strong>
                  {dossier.requerant.personnePhysique.civilite}{" "}
                  {dossier.requerant.personnePhysique.prenom1}{" "}
                  {dossier.requerant.personnePhysique.nom}
                </strong>
              </p>
              {gotoFirstSection && (
                <Button onClick={gotoFirstSection} priority="secondary">
                  Corriger la saisie
                </Button>
              )}
            </>
          )}
          {!dossier.requerant.isPersonneMorale && (
            <>
              <h3>Votre identité</h3>
              <dl className="fr-mb-2w">
                <strong>
                  {dossier.requerant.personnePhysique.civilite}{" "}
                  {dossier.requerant.personnePhysique.prenom1}{" "}
                  {dossier.requerant.personnePhysique.nom}
                </strong>
                <dd>{dossier.requerant.adresse.ligne1}</dd>
                <dd>
                  {dossier.requerant.adresse.codePostal}{" "}
                  {dossier.requerant.adresse.localite}
                </dd>
              </dl>

              {gotoFirstSection && (
                <Button onClick={gotoFirstSection} priority="secondary">
                  Corriger la saisie
                </Button>
              )}
            </>
          )}
        </section>
      </div>
      {/*
            <div className="fr-col-lg-6 fr-col-12">
                <section className="pr-form-section fr-p-4w">
                    <h3>Service enquêteur</h3>
                    <ul className="fr-mb-2w">
                        <li>
                            <u>Nom</u>: {dossier.serviceEnqueteur.nom? <strong>{dossier.serviceEnqueteur.nom}</strong> : <i>non renseigné</i>}
                        </li>
                        <li>
                            <u>Numéro de téléphone</u>: {dossier.serviceEnqueteur.telephone ? <strong>{dossier.serviceEnqueteur.telephone}</strong> : <i>non renseigné</i>}
                        </li>
                        <li>
                            <u>Courriel</u>: {dossier.serviceEnqueteur.courriel ? <strong>{dossier.serviceEnqueteur.courriel}</strong> : <i>non renseigné</i>}
                        </li>
                        <li>
                            <u>Numéro de procès-verbal</u> : {dossier.serviceEnqueteur.numeroPV ? <strong>{dossier.serviceEnqueteur.numeroPV}</strong> : <i>non renseigné</i>}
                        </li>
                        <li>
                            <u>Juridiction</u> : {dossier.serviceEnqueteur.juridiction ? <strong>{dossier.serviceEnqueteur.juridiction}</strong> : <i>non renseignée</i>}
                        </li>
                        <li>
                            <u>Numéro de parquet ou d'instruction</u> : {dossier.serviceEnqueteur.numeroParquet ? <strong>{dossier.serviceEnqueteur.numeroParquet}</strong> : <i>non renseigné</i>}
                        </li>
                        <li>
                            <u>Nom du magistrat</u> : {dossier.serviceEnqueteur.magistrat ? <strong>{dossier.serviceEnqueteur.magistrat}</strong> : <i>non renseigné</i>}
                        </li>
                    </ul>
                    {gotoSecondSection &&
                        <Button
                            onClick={gotoSecondSection}
                            priority="secondary"
                        >Corriger la saisie</Button>
                    }
                </section>
            </div>
            */}
      <div className="fr-col-12">
        <section className="pr-form-section fr-p-4w">
          <h3>Bris de porte</h3>
          <div className="fr-mb-2w">
            <label>Date de l'opération de police judiciaire</label> :{" "}
            {dossier.dateOperationPJ ? (
              <strong>
                {dateFormat(dossier.dateOperationPJ, "dd/MM/yyyy")}
              </strong>
            ) : (
              <i>non renseignée</i>
            )}
          </div>
          <label>À l'adresse suivante :</label>
          <dl className="fr-mb-2w">
            <address>
              <dd>{dossier.adresse.ligne1}</dd>
              <dd>
                {dossier.adresse.codePostal} {dossier.adresse.localite}
              </dd>
            </address>
          </dl>
          <dl className="fr-mb-2w">
            <dd>
              Porte blindée :{" "}
              <strong>{dossier.isPorteBlindee ? "Oui" : "Non"}</strong>
            </dd>
          </dl>
          <div className="fr-mb-2w">
            <label>
              J'effectue ma demande en qualité de :{" "}
              <strong>{dossier.qualiteRequerant}</strong>
            </label>
          </div>
          {/*
                    <dl className="fr-mb-2w">
                        <dd>Attestation remise à
                            : <strong>{dossier.receveurAttestation.civilite} {dossier.receveurAttestation.prenom1} {dossier.receveurAttestation.nom}</strong>
                        </dd>
                        <dd>En qualité de <strong>{dossier.receveurAttestation.qualite}</strong></dd>
                    </dl>
                    */}
          {gotoSecondSection && (
            <Button onClick={gotoSecondSection} priority="secondary">
              Corriger la saisie
            </Button>
          )}
        </section>
      </div>
      <div className="fr-col-12">
        <section className="pr-form-section fr-px-4w">
          <h3>Documents à joindre obligatoirement à votre demande</h3>

          <Document
            documents={dossier.documents.attestation_information}
            lectureSeule={true}
            libelle="Attestation complétée par les forces de l'ordre"
            type={"attestation_information"}
          />

          <Document
            documents={dossier.documents.photo_prejudice}
            lectureSeule={true}
            libelle="Photos de la porte endommagée"
            type={"photo_prejudice"}
          />
          {!dossier.requerant.isPersonneMorale && (
            <Document
              documents={dossier.documents.carte_identite}
              lectureSeule={true}
              libelle="Copie de votre pièce d'identité recto-verso"
              type={"carte_identite"}
            />
          )}

          <Document
            documents={dossier.documents.preuve_paiement_facture}
            lectureSeule={true}
            libelle="Facture acquittée attestant de la réalité des travaux de remise en état à l'identique "
            type={"preuve_paiement_facture"}
          />
          <Document
            documents={dossier.documents.rib}
            lectureSeule={true}
            libelle={
              dossier.requerant.isPersonneMorale
                ? "Relevé d'identité bancaire de votre société"
                : "Votre relevé d'identité bancaire"
            }
            type={"rib"}
          />

          {dossier.requerant.qualiteRequerant === "PRO" && (
            <Document
              documents={dossier.documents.titre_de_propriete}
              lectureSeule={true}
              label="Titre de propriété"
              type={"titre_propriete"}
            />
          )}

          {dossier.requerant.qualiteRequerant === "LOC" && (
            <Document
              documents={dossier.documents.contrat_location}
              lectureSeule={true}
              libelle={"Contrat de location"}
              type={"contrat_location"}
            />
          )}

          {dossier.requerant.qualiteRequerant === "LOC" && (
            <Document
              documents={dossier.documents.non_prise_en_charge_assurance}
              lectureSeule={true}
              libelle={
                "Attestation de non prise en charge par l'assurance habitation"
              }
              type={"non_prise_en_charge_assurance"}
            />
          )}
        </section>
      </div>
    </div>
  );
};

export default Recapitulatif;
