import React from 'react';

const Hidden = ({name,value}) => {
  return (
    <input type="hidden" name={name} value={value} />
  );
}

export default Hidden;
