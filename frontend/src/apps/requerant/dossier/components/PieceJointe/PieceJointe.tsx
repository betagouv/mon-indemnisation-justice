import React, { useContext } from "react";
import { Uploader } from "@/apps/requerant/dossier/components/Uploader";
import { DossierContext } from "@/apps/requerant/dossier/contexts/DossierContext.ts";
import * as Sentry from "@sentry/browser";

export const Document = ({
  documents,
  libelle,
  lectureSeule = false,
  type,
  onRemoved,
  onUploaded,
}: {
  documents: any[];
  libelle: string;
  lectureSeule?: boolean;
  type: string;
  onRemoved?: (document: any) => void;
  onUploaded?: (document: any) => void;
}) => {
  const dossier = useContext(DossierContext);

  const handleRemove = async (document) => {
    fetch(`/requerant/document/${document.id}/${document.filename}`, {
      method: "DELETE",
    })
      .then(() => onRemoved?.(document))
      .catch((e) => {
        Sentry.captureException(e);
      });
  };

  return (
    <>
      <div className="fr-col-12 fr-my-2w">
        <h6
          className={`${documents?.length ? "fr-icon-check-line fr-text-default--success" : "fr-icon-close-line fr-text-default--error"}`}
        >
          {" "}
          {libelle} ({documents?.length || "aucun"} document
          {documents?.length > 1 ? "s" : ""})
        </h6>

        {!lectureSeule && (
          <Uploader dossier={dossier} type={type} onUploaded={onUploaded} />
        )}

        {(documents ?? []).map((document) => (
          <div key={document.id} className="fr-grid-row fr-col-lg-6">
            <a
              className="fr-link"
              target="_blank"
              href={`/requerant/document/${document.id}/${document.filename}`}
            >
              {document.originalFilename}
            </a>
            {!lectureSeule && (
              <button
                className="fr-btn fr-btn--sm fr-icon-delete-line fr-btn--tertiary-no-outline fr-mx-1w"
                onClick={() => handleRemove(document)}
              >
                Retirer
              </button>
            )}
          </div>
        ))}
        {documents?.length === 0 && <i>Aucun document</i>}
      </div>
    </>
  );
};
