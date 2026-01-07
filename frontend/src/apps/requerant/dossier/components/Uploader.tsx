import React, { useMemo, useState } from "react";
import { randomId } from "@/apps/requerant/dossier/services/Random.ts";
import * as Sentry from "@sentry/browser";

export const Uploader = ({
  dossier,
  onUploaded,
  className,
  type,
}: {
  dossier: any;
  onUploaded?: (document: any) => void;
  className?: string;
  type: string;
}) => {
  const MAX_SIZE = 5 * 1024 * 1024;
  const [erreur, setErreur]: [string | null, (erreur: string | null) => void] =
    useState<string | null>(null);

  const id = useMemo<string>(() => randomId(), []);

  const handleFileInput = (ev) => {
    setErreur("");
    const file: File = ev.target.files[0];
    if (file.type) {
      if (
        !["image/jpeg", "image/png", "image/webp", "application/pdf"].includes(
          file.type,
        )
      ) {
        setErreur("Type de fichier nom accepté");
      } else if (file.size > MAX_SIZE) {
        setErreur("Taille de fichier supérieure à 5Mo");
        return;
      } else {
        const data = new FormData();
        data.append("piece-jointe", ev.target.files[0]);
        fetch(`/requerant/document/${dossier.id}/${type}`, {
          method: "POST",
          body: data,
        })
          .then((response) => response.json())
          .then((document) => onUploaded?.(document))
          .catch((e) => {
            Sentry.captureException(e);
            setErreur(
              "Un problème technique est survenu lors du téléversement. Nous vous invitons à ré-essayer.",
            );
          });
      }
    }
  };

  return (
    <div className={`fr-my-2w fr-upload-group ${className}`}>
      <label className="fr-label" htmlFor={id}>
        Document à téléverser
      </label>
      <input
        id={id}
        className="fr-upload"
        type="file"
        multiple={false}
        accept="image/jpeg, image/png, image/webp, application/pdf"
        onChange={handleFileInput}
      />
      <span className="fr-hint-text">
        Vous pouvez déposer plusieurs fichiers à la suite.
        <br />
        Taille maximale : 5 Mo, formats supportés : jpg, png, pdf, webp.
      </span>
      {erreur && <p className="fr-error-text">{erreur}</p>}
    </div>
  );
};
