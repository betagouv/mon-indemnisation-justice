import React, { ReactNode } from "react";

export const TitreSection = ({ children }: { children: ReactNode }) => {
  return (
    <div className="fr-grid-row fr-my-2w">
      <h6 className="fr-m-0 fr-text-label--blue-france">{children}</h6>
    </div>
  );
};
