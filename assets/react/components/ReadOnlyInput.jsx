import React from 'react';

const ReadOnlyInput = ({label,value}) => {
  return (
    <div className="fr-input-group">
      <label className="fr-label">{label}</label>
      <b>{value}</b>
    </div>
  );
}

export default ReadOnlyInput;
