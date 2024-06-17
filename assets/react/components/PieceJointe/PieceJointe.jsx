import React,{useState,useEffect} from 'react';
import { Uploader } from '../Uploader';
import { Br,Loading } from '../../utils/fundamental';
import { trans, GLOBAL_WAITING
} from '../../../translator';

export const Document = ({liasseDocumentaireIri,type,label,hint_text="",readonly=false}) => {
  const [loading,setLoading]=useState(false);
  const [documents,setDocuments]=useState([]);
  const [selectedFile,setSelectedFile]=useState(null);
  const [reload, setReload]=useState(false);
  const toggleReload = () => setReload(!reload);
  useEffect(() => {
    if(!selectedFile)
      return;
    const tmp=documents;
    tmp.push(selectedFile);
    setDocuments(tmp);
    toggleReload();
  },[selectedFile]);

  useEffect(() => {
    if(!reload) { return; }
    toggleReload();
  },[reload]);

  useEffect(() => {
    if(true===loading)
      return;
    const url = Routing.generate('_api_document_get_collection',{
      liasseDocumentaire: liasseDocumentaireIri,
      type: type
    });

    fetch(url)
      .then((response) => response.json())
      .then((data) => {
        const count = data["hydra:totalItems"];
        const items = data["hydra:member"];
        setDocuments(items);
        setLoading(true);
      })
      .catch(() => setLoading(true))
  },[]);

  return (
    <>
      {loading &&
      <>
        <div className="fr-col-12">
        {readonly &&
          <label className="fr-label">{label}</label>
        }
        {!readonly &&
          <Uploader
            hint_text={hint_text}
            label={label}
            type={type}
            liasseDocumentaireIri={liasseDocumentaireIri}
            selectedFile={selectedFile}
            setSelectedFile={setSelectedFile}
          />
        }
        </div>
        <div className="fr-col-12">
        {documents.map((item) =>
            <div key={item.id}>
              <a
                className="fr-link"
                target="_blank"
                href={Routing.generate('app_document_download',{id:item.id, filename: item.filename})}
              >
              {item.originalFilename}
              </a>&nbsp;|&nbsp;
            </div>
          )
        }
        </div>
      </>
      }
      {!loading &&
      <Loading/>
      }
    </>
  );
}
