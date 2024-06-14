import React from 'react';

const Submit = function({label,type='primary'}) {

  const _type = (type=='secondary') ? 'fr-btn--secondary' : '';

  return (
    <button className={"fr-btn "+_type}>{label}</button>
  );
}

export default Submit;
