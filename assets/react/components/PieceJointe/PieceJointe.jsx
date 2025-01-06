import React from 'react';
import {Uploader} from '@/react/components/Uploader';


export const Document = ({documents, libelle, lectureSeule, liasseDocumentaire, type, onRemoved, onUploaded}) => {
    
    const handleRemove = (document, e) => {
        fetch(`/document/${document.id}/${document.filename}`,{ method: "DELETE" })
          .then(() => onRemoved(document))
          .catch(() => {});
    }

    return (
        <>
            <div className="fr-col-12 fr-my-1w">
                {lectureSeule &&
                    <h6>{libelle}</h6>
                }
                <div className="fr-pl-3w">
                {!lectureSeule &&
                    <Uploader
                        libelle={libelle}
                        type={type}
                        liasseDocumentaire={liasseDocumentaire}
                        onUploaded={onUploaded}
                    />
                }
                {documents.map((document) =>
                    <div key={document.id} className="fr-grid-row fr-col-6">
                        {!lectureSeule &&
                            <button className="fr-btn fr-btn--sm fr-icon-delete-line fr-btn--tertiary-no-outline fr-mx-1w" onClick={() => handleRemove(document)}>
                                Retirer
                            </button>
                        }
                        <a
                            className="fr-link"
                            target="_blank"
                            href={`/document/${document.id}/${document.filename}`}
                        >
                            {document.originalFilename}
                        </a>
                    </div>
                )}
                {documents.length === 0 &&
                    <i>Aucun document</i>
                }
                </div>
            </div>
        </>
    );
}
