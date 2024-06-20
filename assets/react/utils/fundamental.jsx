import React,{useEffect,useState} from 'react';
import {trans, GLOBAL_WAITING } from '../../translator';
import { ContentState, convertToRaw, convertFromRaw } from 'draft-js';
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

  const _contentState = ContentState.createFromText(value);
  const raw = convertToRaw(_contentState);
  const [contentState,setContentState]=useState(raw);
  useEffect(() => {
    setValue(stateToHTML(convertFromRaw(contentState)));
  },[contentState]);
  return (
    <>
      <label className="fr-label">{label}</label>
      <Editor
        defaultContentState={contentState}
        onContentStateChange={setContentState}
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
