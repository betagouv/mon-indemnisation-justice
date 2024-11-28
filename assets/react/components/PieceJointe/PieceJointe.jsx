import React from 'react';
import {Uploader} from '@/react/components/Uploader';


export const Document = ({documents, libelle, lectureSeule, liasseDocumentaire, type, onRemoved, onUploaded}) => {

    const handleRemove = (document, e) => {
        fetch(router.removeDocument,{ method: "DELETE" })
          .then(() => onRemoved(document))
          .catch(() => {});
    }

    return (
        <>
            <div className="fr-col-12 fr-m-3w">
                {lectureSeule ?
                    <h6>{libelle}</h6> :
                    <h4>{libelle}</h4>
                }
                <div className="fr-pl-3w">
                {documents.map((document) =>
                    <div key={document.id} className="fr-grid-row">
                        <div className="fr-col-lg-10 fr-col-12 fr-my-1w">
                            <a
                                className="fr-link"
                                target="_blank"
                                href={`/document/${document.id}/${document.filename}`}
                            >
                                {document.originalFilename}
                            </a>
                        </div>
                        {!lectureSeule &&
                            <div className="fr-col-lg-2 fr-col-12 fr-btns-group fr-btns-group--sm fr-btns-group--right">
                                <button className="fr-btn fr-icon-delete-line fr-btn--tertiary-no-outline" onClick={() => handleRemove(document)}>
                                    Retirer
                                </button>
                            </div>
                        }
                    </div>
                )}
                {documents.length === 0 &&
                    <i>Aucun document</i>
                }
                {!lectureSeule &&
                    <Uploader
                        label={libelle}
                        type={type}
                        liasseDocumentaire={liasseDocumentaire}
                        onUploaded={onUploaded}
                    />
                }
                </div>
            </div>
        </>
    );
}
