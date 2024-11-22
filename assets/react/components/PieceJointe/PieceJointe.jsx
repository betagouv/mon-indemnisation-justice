import React from 'react';
import { Uploader } from '../Uploader';


export const Document = ({documents, libelle, description, lectureSeule, type}) => {

  return (
    <>
      <div className="fr-col-12">
        {lectureSeule &&
          <label className="fr-label">{libelle}</label>
        }
        {!lectureSeule &&
          <Uploader
            hint_text={description || ""}
            label={libelle}
            type={type}
            //liasseDocumentaireIri={liasseDocumentaireIri}
          />
        }
        </div>
        <div className="fr-col-12">
        {documents.map((document) =>
            <div key={document.id}>
              <a
                className="fr-link"
                target="_blank"
                href={Routing.generate('app_document_download',{id:document.id, filename: document.filename})}
              >
              {document.originalFilename}
              </a>&nbsp;|&nbsp;
            </div>
          )
        }
        </div>
    </>
  );
}
