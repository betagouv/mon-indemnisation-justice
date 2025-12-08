import React, { useContext } from "react";
import { format as dateFormat } from "date-fns";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { DossierContext } from "@/apps/requerant/dossier/contexts/DossierContext.ts";
import { Document } from "@/apps/requerant/dossier/components/PieceJointe/PieceJointe";
import styles from "./Recapitulatif.module.css";
import { dateSimple } from "@/common/services/date.ts";
import { capitaliser } from "@/common/services/divers.ts";
import QualiteRequerant from "@/apps/requerant/dossier/models/QualiteRequerant.ts";

const Recapitulatif = ({
  gotoFirstSection,
  gotoSecondSection,
  gotoThirdSection,
}: {
  gotoFirstSection: () => void;
  gotoSecondSection: () => void;
  gotoThirdSection: () => void;
}) => {
  const dossier = useContext(DossierContext);

  return (
    <div className="fr-grid-row fr-mb-4w">
      <section className="fr-col-lg-6 fr-col-12">
        <>
          {dossier.requerant.isPersonneMorale ? (
            <>
              <h3>Identité de la société</h3>
              <div className="fr-col-12">
                <dl>
                  <dt className={styles.dt}>Raison sociale :</dt>
                  <dd>{dossier.requerant.personneMorale.raisonSociale}</dd>
                  <dt className={styles.dt}>SIREN / SIRET : </dt>
                  <dd>{dossier.requerant.personneMorale.sirenSiret}</dd>
                  <dt className={styles.dt}>Adresse :</dt>
                  <dd>
                    {dossier.requerant.adresse.ligne1}
                    {dossier.requerant.adresse.ligne2 ? (
                      <>
                        <br />
                        {dossier.requerant.adresse.ligne2}
                      </>
                    ) : (
                      ""
                    )}
                    <br />
                    {dossier.requerant.adresse.codePostal}{" "}
                    {dossier.requerant.adresse.localite}
                  </dd>
                  <dt className={styles.dt}>Représentée par :</dt>
                  <dd>
                    {dossier.requerant.personnePhysique.civilite}{" "}
                    {capitaliser(dossier.requerant.personnePhysique.prenom1)}{" "}
                    {dossier.requerant.personnePhysique.nom?.toUpperCase()}
                  </dd>
                </dl>
              </div>
            </>
          ) : (
            <>
              <h3>Votre identité</h3>
              <div className="fr-col-12 fr-col-lg-6">
                <dl>
                  <dt className={styles.dt}>Nom et prénom :</dt>
                  <dd>
                    {dossier.requerant.personnePhysique.civilite}{" "}
                    {capitaliser(dossier.requerant.personnePhysique.prenom1)}{" "}
                    {dossier.requerant.personnePhysique.nom?.toUpperCase()}
                  </dd>
                  <dt className={styles.dt}>
                    Né
                    {dossier.requerant.personnePhysique.civilite == "MME"
                      ? "e"
                      : ""}{" "}
                    :
                  </dt>
                  <dd>
                    {dossier.requerant.personnePhysique.dateNaissance ? (
                      `le ${dateSimple(
                        new Date(
                          dossier.requerant.personnePhysique.dateNaissance,
                        ),
                      )}`
                    ) : (
                      <i>non renseigné</i>
                    )}
                  </dd>
                </dl>
              </div>
              <div className="fr-col-12 fr-col-lg-6">
                <dl>
                  <dt className={styles.dt}>Adresse :</dt>
                  <dd>
                    {dossier.requerant.adresse.ligne1}
                    <br />
                    {dossier.requerant.adresse.codePostal}{" "}
                    {dossier.requerant.adresse.localite}
                  </dd>
                  <dt className={styles.dt}>Numéro de téléphone :</dt>
                  <dd>{dossier.requerant.personnePhysique.telephone}</dd>
                </dl>
              </div>
            </>
          )}

          <Button
            className="fr-my-1w"
            size="small"
            priority="secondary"
            iconId="fr-icon-pencil-line"
            children="Reprendre la saisie"
            onClick={gotoFirstSection}
            iconPosition="right"
          />
        </>
      </section>

      <section className="fr-col-12 fr-col-lg-6">
        <h3>Bris de porte</h3>
        <dl>
          <dt className={styles.dt}>
            Date de l'opération de police judiciaire :
          </dt>
          <dd>
            {dossier.dateOperationPJ ? (
              <>{dateFormat(dossier.dateOperationPJ, "dd/MM/yyyy")}</>
            ) : (
              <i>non renseignée</i>
            )}
          </dd>

          <dt className={styles.dt}>À l'adresse suivante :</dt>
          <dd>
            {dossier.adresse.ligne1}
            <br />
            {dossier.adresse.codePostal} {dossier.adresse.localite}
          </dd>

          <dt className={styles.dt}>Il s'agit d'une porte blindée :</dt>
          <dd>{dossier.isPorteBlindee ? "Oui" : "Non"}</dd>

          <dt className={styles.dt}>J'effectue ma demande en qualité de :</dt>
          <dd>{QualiteRequerant[dossier.qualiteRequerant]}</dd>

          <dt className={styles.dt}>Description de l'intervention :</dt>
          <dd
            dangerouslySetInnerHTML={{
              __html: (dossier.descriptionRequerant || "").replaceAll(
                /\n/g,
                "</br>",
              ),
            }}
          />
        </dl>

        <Button
          className="fr-my-1w"
          size="small"
          priority="secondary"
          iconId="fr-icon-pencil-line"
          children="Reprendre la saisie"
          onClick={gotoSecondSection}
          iconPosition="right"
        />
      </section>

      <section className="fr-col-12 fr-py-4w">
        <h3>Documents à joindre obligatoirement à votre demande</h3>

        {!dossier.issuDeclarationFDO && (
          <Document
            documents={dossier.documents.attestation_information}
            lectureSeule={true}
            libelle="Attestation complétée par les forces de l'ordre"
            type={"attestation_information"}
          />
        )}

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
          documents={dossier.documents.facture}
          lectureSeule={true}
          libelle="Facture acquittée attestant de la réalité des travaux de remise en état à l'identique"
          type={"facture"}
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

        {dossier.qualiteRequerant === "PRO" && (
          <Document
            documents={dossier.documents.titre_propriete}
            lectureSeule={true}
            libelle="Titre de propriété"
            type={"titre_propriete"}
          />
        )}

        {dossier.qualiteRequerant === "LOC" && (
          <Document
            documents={dossier.documents.contrat_location}
            lectureSeule={true}
            libelle={"Contrat de location"}
            type={"contrat_location"}
          />
        )}

        {dossier.qualiteRequerant === "LOC" && (
          <Document
            documents={dossier.documents.non_prise_en_charge_bailleur}
            lectureSeule={true}
            libelle={
              "Attestation de non prise en charge par l'assurance habitation"
            }
            type={"non_prise_en_charge_bailleur"}
          />
        )}
        <Document
          documents={dossier.documents.non_prise_en_charge_assurance}
          lectureSeule={true}
          libelle={
            "Attestation de non prise en charge par l'assurance habitation"
          }
          type={"non_prise_en_charge_assurance"}
        />

        <Button
          className="fr-my-1w"
          size="small"
          priority="secondary"
          iconId="fr-icon-pencil-line"
          children="Reprendre la saisie"
          onClick={gotoThirdSection}
          iconPosition="right"
        />
      </section>
    </div>
  );
};

export default Recapitulatif;
