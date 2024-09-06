import React,{ useState } from 'react';

import { castNumber } from '../utils/cast';

export const Uploader = ({liasseDocumentaireIri, type, setSelectedFile, label=null,hint_text=null}) => {
  const MAX_SIZE=2048*1000*8;
  const [errMsg,setErrMsg]=useState("");

  const handleFileInput = (ev) => {
    setErrMsg("");
    const file = ev.target.files[0];
    if (file.size > MAX_SIZE) {
      setErrMsg("Taille de fichier supérieure à 16Mo");
      return;
    }

    const route = Routing.generate('app_document_upload',{type: type, id: castNumber(liasseDocumentaireIri)});
    const data = new FormData();
    data.append('file', ev.target.files[0]);
    fetch(route,{ method: "POST", body: data })
      .then((response) => response.json())
      .then((data) => setSelectedFile(data))
      .catch(() => {})
    ;
  }

  const getGrpClassnames = (msg) => "fr-upload-group "+(msg?"fr-input-group--error":"");
  const _label= label ?? "Ajouter des fichiers";
  const _hint_text= hint_text ?? "Taille maximale : 2 Mo. Formats supportés : jpg, png, pdf.";

  return (
    <div className={getGrpClassnames(errMsg)}>
      <label className="fr-label" htmlFor="file-upload">{_label}
          <span className="fr-hint-text">{_hint_text}</span>
      </label>
      <input className="fr-upload" type="file" onChange={handleFileInput} />
      {errMsg &&
      <p className="fr-error-text">{errMsg}</p>
      }
    </div>
  );
}
