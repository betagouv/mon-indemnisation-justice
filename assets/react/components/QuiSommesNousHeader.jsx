import React from 'react';
import parse from 'html-react-parser';
import { trans, HOMEPAGE_QUI_SOMMES_NOUS_TITLE, HOMEPAGE_QUI_SOMMES_NOUS_CHAPO } from '../../translator';

const QuiSommesNousHeader = function() {
  return (
    <>
      <h1>{trans(HOMEPAGE_QUI_SOMMES_NOUS_TITLE)}</h1>
      {parse(trans(HOMEPAGE_QUI_SOMMES_NOUS_CHAPO))}
    </>
  );
}

export default QuiSommesNousHeader;
