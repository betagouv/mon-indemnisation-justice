import React, {useRef, useState} from 'react';
import { randomId } from "@/react/services/Random.ts";

export const Uploader = ({liasseDocumentaire, libelle, onUploaded, type }) => {

  const MAX_SIZE=2048*1000*8;
  const [erreur,setErreur]=useState("");

  const id = useRef(randomId());

  const handleFileInput = (ev) => {
    setErreur("");
    const file = ev.target.files[0];
    if (file.size > MAX_SIZE) {
      setErreur("Taille de fichier supérieure à 2Mo");
      return;
    }

    const data = new FormData();
    data.append('file', ev.target.files[0]);
    fetch(`/document/${liasseDocumentaire.id}/${type}`,{ method: "POST", body: data })
      .then((response) => response.json())
      .then((document) => onUploaded(document))
      .catch(() => {})
    ;
  }

  return (
      <div className={`fr-my-2w fr-upload-group ${erreur ? 'fr-input-group--error' : ''}`}>
        <label className="fr-label" htmlFor={id}>
            {/*<span className="fr-icon-upload-2-line fr-mr-1w" aria-hidden="true"></span>*/}
            {libelle}
        </label>
        <input id={id} className="fr-upload" type="file" onChange={handleFileInput}/>
        <span className="fr-hint-text">
            Taille maximale : 2 Mo. Formats supportés : jpg, png, pdf.
          </span>
        {erreur &&
            <p className="fr-error-text">{erreur}</p>
        }
      </div>
  );
}
