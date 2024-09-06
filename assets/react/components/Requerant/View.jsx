import React  from 'react';
import ReadOnlyInput from '../ReadOnlyInput';

const RequerantView = function({
  qualiteRequerant,
  precisionRequerant
}) {
  return (
    <>
      <div className="fr-grid-row">
        <div className="fr-col-4">
          <ReadOnlyInput
            label="J'effectue ma demande en qualité de"
            value={qualiteRequerant.libelle}
          />
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-7">
          <ReadOnlyInput
            label="Préciser votre qualité"
            value={precisionRequerant}
          />
        </div>
      </div>
    </>
  );
}

export default RequerantView;
