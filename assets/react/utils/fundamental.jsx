import React from 'react';
import {trans, GLOBAL_WAITING } from '../../translator';

export const Br = ({space=1}) => {
  const rows = [];
  for(let i=0;i<space;i++) { rows.push(<br key={i}/>) }
  return (rows);
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
