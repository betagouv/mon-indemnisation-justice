import React,{useState,useEffect} from 'react';
import { EditorState } from 'draft-js';
import { Editor } from 'react-draft-wysiwyg';
import 'react-draft-wysiwyg/dist/react-draft-wysiwyg.css';

const FormulaireSimplifie = ({prejudice}) => {

  useEffect(() => setLoading(true),[]);
  const [editorState, setEditorState] = useState(() => EditorState.createEmpty());
  const [loading, setLoading] = useState(false);

  return (
    <div className="fr-grid-row">
      <div className="fr-col-12">
        {loading &&
        <Editor
          editorState={editorState}
          onChange={setEditorState}
        />
        }
      </div>
    </div>
  );
}

export default FormulaireSimplifie;
