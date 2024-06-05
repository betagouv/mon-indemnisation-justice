import React,{useState,useEffect,useRef} from 'react';

import { castNumber } from '../utils/cast';
import { Upload } from "@codegouvfr/react-dsfr/Upload";
import { trans, DOCUMENT_DEFAULT, DOCUMENT_HINT_TEXT
} from '../../translator';

export const Uploader = ({liasseDocumentaireIri,type,label=null,hint_text=null}) => {
  const MAX_SIZE=2048*1000;
  const [errMsg,setErrMsg]=useState("");

  const handleFileInput = (ev) => {
    const file = ev.target.files[0];
    if (file.size > MAX_SIZE) {
      setErrMsg("Taille de fichier supérieure à 2Mo");
      return;
    }
    const route = Routing.generate('app_document_upload',{type: type, id: castNumber(liasseDocumentaireIri)});
    const data = new FormData();
    data.append('file', ev.target.files[0]);
    fetch(route,{ method: "POST", body: data })
      .then((response) => response.json())
      .then((data) => {console.log(data)})
      .catch(() => {})
    ;
  }

  const getGrpClassnames = (msg) => "fr-upload-group "+(msg?"fr-input-group--error":"");
  const _label=label??trans(DOCUMENT_DEFAULT);
  const _hint_text=hint_text??trans(DOCUMENT_HINT_TEXT);
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
