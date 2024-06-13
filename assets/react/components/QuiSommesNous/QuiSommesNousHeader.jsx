import React from 'react';
import parse from 'html-react-parser';
import { trans, HOMEPAGE_QUI_SOMMES_NOUS_TITLE, HOMEPAGE_QUI_SOMMES_NOUS_CHAPO } from '../../../translator';
import './main.css';

const QuiSommesNousHeader = function() {
  return (
    <section className="pr-whoami">
      <div className="fr-container">
        <div className="fr-pt-6w fr-pb-12w">
          <div className="pr-whoami_text fr-p-4w">
            <h1>{trans(HOMEPAGE_QUI_SOMMES_NOUS_TITLE)}</h1>
            {parse(trans(HOMEPAGE_QUI_SOMMES_NOUS_CHAPO))}
          </div>
        </div>
      </div>
    </section>
  );
}

export default QuiSommesNousHeader;
