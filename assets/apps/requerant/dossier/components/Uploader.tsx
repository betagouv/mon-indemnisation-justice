import React, { useMemo, useRef, useState } from "react";
import { randomId } from "@/apps/requerant/dossier/services/Random.ts";

export const Uploader = ({
  dossier,
  libelle,
  onUploaded,
  type,
}: {
  dossier: any;
  libelle: string;
  onUploaded: (document: any) => void;
  type: string;
}) => {
  const MAX_SIZE = 5 * 1024 * 1024;
  const [erreur, setErreur]: [string | null, (erreur: string | null) => void] =
    useState(null);

  const id = useMemo<string>(() => randomId(), []);

  const handleFileInput = (ev) => {
    setErreur("");
    const file: File = ev.target.files[0];
    if (
      ![
        "image/jpeg",
        "image/png",
        "image/gif",
        "image/webp",
        "application/pdf",
      ].includes(file.type)
    ) {
      setErreur("Type de fichier nom accepté");
    } else if (file.size > MAX_SIZE) {
      setErreur("Taille de fichier supérieure à 5Mo");
      return;
    } else {
      const data = new FormData();
      data.append("file", ev.target.files[0]);
      fetch(`/requerant/document/${dossier.id}/${type}`, {
        method: "POST",
        body: data,
      })
        .then((response) => response.json())
        .then((document) => onUploaded(document))
        .catch(() => {});
    }
  };

  return (
    <div className="fr-my-2w fr-upload-group">
      <label className="fr-label" htmlFor={id}>
        {/*<span className="fr-icon-upload-2-line fr-mr-1w" aria-hidden="true"></span>*/}
        {libelle}
      </label>
      <input
        id={id}
        className="fr-upload"
        type="file"
        onChange={handleFileInput}
      />
      <span className="fr-hint-text">
        Taille maximale : 5 Mo. Formats supportés : jpg, png, pdf.
      </span>
      {erreur && <p className="fr-error-text">{erreur}</p>}
    </div>
  );
};
