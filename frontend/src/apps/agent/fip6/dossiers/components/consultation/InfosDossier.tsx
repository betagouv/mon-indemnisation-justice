import React from "react";
import { Agent, DossierDetail } from "@/common/models";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { dateSimple } from "@/common/services/date.ts";
import { capitaliser } from "@/common/services/divers.ts";

export const InfosDossier = ({
  dossier,
  agent,
}: {
  dossier: DossierDetail;
  agent: Agent;
}) => {
  return (
    <>
      <div className="fr-grid-column">
        <h3>Informations sur le dossier </h3>

        <div className="fr-grid-row">
          <div className="fr-col-6">
            <h5>Déclaration du requérant</h5>

            <ul className="fr-list">
              <li>
                <b>Nom: </b> {dossier.requerant.nomComplet()}
              </li>
              <li>
                <b>Prénom(s): </b>{" "}
                {dossier.requerant.prenoms.filter((p) => !!p).join(", ")}
              </li>
              <li>
                <b>Né{dossier.requerant.estFeminin() ? "e" : ""}</b> le{" "}
                {dossier.requerant.dateNaissance ? (
                  <>{dateSimple(dossier.requerant.dateNaissance)}</>
                ) : (
                  <i>non renseigné</i>
                )}
                , à{" "}
                {dossier.requerant.communeNaissance ? (
                  <>
                    {dossier.requerant.communeNaissance}{" "}
                    {dossier.requerant.paysNaissance
                      ? `(${dossier.requerant.paysNaissance})`
                      : ""}
                  </>
                ) : (
                  <i>non renseigné</i>
                )}
              </li>
              {dossier.requerant.estPersonneMorale() && (
                <li>
                  Représentant
                  {dossier.requerant.estFeminin() ? "e" : ""} légal
                  {dossier.requerant.estFeminin() ? "e" : ""} de la société{" "}
                  <b>{dossier.requerant.raisonSociale}</b> (SIREN:{" "}
                  <b>{dossier.requerant.siren}</b>)
                </li>
              )}
              <li>
                <b>Adresse courriel: </b>{" "}
                {dossier.enAttenteInstruction() &&
                agent.estRedacteur() &&
                !agent.estValidateur() &&
                !agent.estBetagouv() ? (
                  <>
                    <span aria-describedby="tooltip-requerant-courriel">{`${dossier.requerant.courriel.substring(0, 2)}${"*".repeat(dossier.requerant.courriel.length - 2)}`}</span>
                    <span
                      className="fr-tooltip fr-placement"
                      id="tooltip-requerant-courriel"
                      role="tooltip"
                      aria-hidden="true"
                    >
                      Démarrer l'instruction pour révéler
                    </span>
                  </>
                ) : (
                  <>
                    <span>{dossier.requerant.courriel}</span>
                    <Button
                      iconId="fr-icon-clipboard-line"
                      onClick={() =>
                        navigator.clipboard.writeText(
                          dossier.requerant.courriel,
                        )
                      }
                      priority="tertiary no outline"
                      title="Copier dans le presse papier"
                      size="small"
                    />
                  </>
                )}
              </li>
              <li>
                <b>N° téléphone: </b>
                {dossier.requerant.telephone ? (
                  <>
                    {dossier.enAttenteInstruction() &&
                    agent.estRedacteur() &&
                    !agent.estValidateur() &&
                    !agent.estBetagouv() ? (
                      <>
                        <span aria-describedby="tooltip-requerant-telephone">{`${dossier.requerant.telephone.substring(0, 2)}${"*".repeat(dossier.requerant.telephone.length - 2)}`}</span>
                        <span
                          className="fr-tooltip fr-placement"
                          id="tooltip-requerant-telephone"
                          role="tooltip"
                          aria-hidden="true"
                        >
                          Démarrer l'instruction pour révéler
                        </span>
                      </>
                    ) : (
                      <>
                        {dossier.requerant.telephone}
                        <Button
                          style={{ display: "inline-block" }}
                          iconId="fr-icon-clipboard-line"
                          onClick={() =>
                            navigator.clipboard.writeText(
                              dossier.requerant.telephone ?? "",
                            )
                          }
                          priority="tertiary no outline"
                          title="Copier dans le presse papier"
                          size="small"
                        />
                      </>
                    )}
                  </>
                ) : (
                  <i>non renseigné</i>
                )}
              </li>
              <li>
                <b>Survenu à l'adresse: </b>{" "}
                {dossier.adresse.estRenseignee() ? (
                  <>{dossier.adresse.libelle()}</>
                ) : (
                  <i>non renseigné</i>
                )}
              </li>
              <li>
                <b>Le :</b>{" "}
                {dossier.dateOperation ? (
                  <>
                    {dateSimple(dossier.dateOperation)}, à{" "}
                    {dossier.dateOperation?.toLocaleString("fr-FR", {
                      hour: "numeric",
                      minute: "numeric",
                    })}
                  </>
                ) : (
                  <i>non renseigné</i>
                )}
              </li>
              <li>
                <b>Porte blindée: </b>{" "}
                {dossier.estPorteBlindee !== null ? (
                  <>{dossier.estPorteBlindee ? "oui" : "non"}</>
                ) : (
                  <i>non renseigné</i>
                )}
              </li>
              {!!dossier.testEligibilite && (
                <>
                  <li>
                    <b>
                      Était visé
                      {dossier.requerant.estFeminin() ? "e" : ""} par
                      l'opération des Forces de l'ordre ?{" "}
                    </b>{" "}
                    <>{dossier.testEligibilite.estVise ? "Oui" : "Non"}</>
                  </li>
                  <li>
                    <b>Hébergeait la personne recherchée ?</b>{" "}
                    <>{dossier.testEligibilite.estHebergeant ? "Oui" : "Non"}</>
                  </li>
                  <li>
                    <b>Situation par rapport au logement ? </b>
                    {dossier.testEligibilite.rapportAuLogement}
                  </li>
                  <li>
                    <b>A contacté l'assurance ?</b>{" "}
                    <>
                      {dossier.testEligibilite.aContacteAssurance
                        ? "Oui"
                        : "Non"}
                    </>
                  </li>
                  {null !== dossier.testEligibilite.aContacteBailleur && (
                    <li>
                      <b>A contacté le bailleur ?</b>{" "}
                      <>
                        {dossier.testEligibilite.aContacteBailleur
                          ? "Oui"
                          : "Non"}
                      </>
                    </li>
                  )}
                </>
              )}
              {dossier.descriptionRequerant && (
                <li>
                  <b>Commentaires du requérant:</b>
                  <blockquote
                    dangerouslySetInnerHTML={{
                      __html: dossier.descriptionRequerant.replaceAll(
                        /\n/g,
                        "</br>",
                      ),
                    }}
                  ></blockquote>
                </li>
              )}
            </ul>
          </div>

          {dossier.declarationFDO && (
            <div className="fr-col-6">
              <h5>Déclaration des FDO</h5>

              <ul>
                <li>
                  <b>Survenu à l'adresse: </b>{" "}
                  {dossier.declarationFDO.adresse?.ligne1}{" "}
                  {dossier.declarationFDO.adresse?.codePostal}{" "}
                  {dossier.declarationFDO.adresse?.localite}
                </li>
                <li>
                  <b>Le :</b> {dateSimple(dossier.declarationFDO.dateOperation)}
                </li>
                <li>
                  <b>Agent rapporteur</b>{" "}
                  {dossier.declarationFDO.agent?.nomComplet()}
                  <ul>
                    <li>{dossier.declarationFDO.agent?.courriel}</li>
                    <li>{dossier.declarationFDO.agent?.telephone}</li>
                  </ul>
                </li>
                <li>
                  <b>S’agissait-il d’une erreur opérationnelle ? </b>
                  {dossier.declarationFDO.estErreur === "DOUTE"
                    ? "J'ai un doute"
                    : capitaliser(dossier.declarationFDO.estErreur as string)}
                </li>
                {dossier.declarationFDO.descriptionErreur && (
                  <li>
                    <b>Explications: </b>
                    <blockquote
                      dangerouslySetInnerHTML={{
                        __html: (
                          dossier.declarationFDO.descriptionErreur ?? ""
                        ).replaceAll(/\n/g, "</br>"),
                      }}
                    />
                  </li>
                )}
                <li>
                  <b>Service enquêteur</b>{" "}
                  {dossier.declarationFDO.procedure.serviceEnqueteur}
                </li>
                <li>
                  <b>Numéro de procédure</b>{" "}
                  {dossier.declarationFDO.procedure.numeroProcedure}
                </li>
                <li>
                  <b>Juridiction ou parquet</b>{" "}
                  {dossier.declarationFDO.procedure.juridictionOuParquet}
                </li>
                <li>
                  <b>Nom du magistrat</b>{" "}
                  {dossier.declarationFDO.procedure.nomMagistrat}
                </li>

                <li>
                  <b>Requérant : </b>
                  {dossier.declarationFDO.coordonneesRequerant ? (
                    <>
                      {dossier.declarationFDO.coordonneesRequerant.prenom}{" "}
                      {dossier.declarationFDO.coordonneesRequerant.nom.toUpperCase()}
                      <ul>
                        <li>
                          {dossier.declarationFDO.coordonneesRequerant.courriel}
                        </li>
                        <li>
                          {
                            dossier.declarationFDO.coordonneesRequerant
                              .telephone
                          }
                        </li>
                      </ul>
                    </>
                  ) : (
                    <i>non renseigné</i>
                  )}
                </li>
                <li>
                  <b>Précisions:</b>{" "}
                  {dossier.declarationFDO.precisionsRequerant ? (
                    <blockquote
                      dangerouslySetInnerHTML={{
                        __html:
                          dossier.declarationFDO.precisionsRequerant.replaceAll(
                            /\n/g,
                            "</br>",
                          ),
                      }}
                    />
                  ) : (
                    <i>non renseignées</i>
                  )}
                </li>
              </ul>
            </div>
          )}
        </div>
      </div>
    </>
  );
};
