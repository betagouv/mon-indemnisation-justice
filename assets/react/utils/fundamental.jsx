import React,{useEffect,useState} from 'react';
import {trans, GLOBAL_WAITING } from '../../translator';
import { ContentState, EditorState, convertToRaw, convertFromRaw,convertFromHTML } from 'draft-js';
import { Editor } from 'react-draft-wysiwyg';
import { stateToHTML } from 'draft-js-export-html';
import 'react-draft-wysiwyg/dist/react-draft-wysiwyg.css';
import './main.css';

export const Br = ({space=1}) => {
  const rows = [];
  for(let i=0;i<space;i++) { rows.push(<br key={i}/>) }
  return (rows);
}

export const Wysiwyg = ({label,value,setValue}) => {
  const _value = value ? value : "<p></p>";
  const contentDataState = ContentState.createFromBlockArray(convertFromHTML(_value));
  const editorDataState = EditorState.createWithContent(contentDataState);
  const [editorState,setEditorState]=useState(editorDataState);

  useEffect(() => {
    const content = editorState.getCurrentContent();
    setValue(stateToHTML(content));
  },[editorState]);

  return (
    <>
      <label className="fr-label">{label}</label>
      <Editor
        editorState={editorState}
        onEditorStateChange={setEditorState}
        wrapperClassName="wrapper-class"
        editorClassName="editor-class"
        toolbarClassName="toolbar-class"
        toolbar={{
          options: ['inline','textAlign'],
          inline: {
            options: ['bold','italic','underline']
          }
        }}
      />
    </>
  );
}
export const Hidden = ({name,value}) => (<input type="hidden" name={name} value={value} />);

export const Submit = ({label,type='primary',disabled=false}) => {
  const _type = (type=='secondary') ? 'fr-btn--secondary' : '';
  return (
    <button className={"fr-btn "+_type} disabled={disabled}>{label}</button>
  );
}

export const Loading = () => {
  return (
    <h5>{trans(GLOBAL_WAITING)}</h5>
  );
}
