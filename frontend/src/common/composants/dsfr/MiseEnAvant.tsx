import artworkOvoid from "@codegouvfr/react-dsfr/dsfr/artwork/background/ovoid.svg";
import React, { type ReactNode } from "react";

export type MiseEnAvantProps = {
  id?: string;
  className?: string;
  pictogrammeUrl: string;
  children: ReactNode;
  action: ReactNode;
};

export const MiseEnAvant = ({
  pictogrammeUrl,
  children,
  action,
  ...props
}: MiseEnAvantProps) => {
  return (
    <div {...props}>
      <div className="fr-col-12  fr-grid-row fr-grid-row--gutters fr-grid-row--middle fr-grid-row--center">
        <div className="fr-py-0 fr-col-12 fr-col-md-6 fr-col-offset-md-1">
          {children}
        </div>
        <div className="fr-col-12 fr-col-md-3 fr-col-offset-md-1 fr-px-6w fr-px-md-0 fr-py-0">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            className="fr-responsive-img fr-artwork"
            aria-hidden="true"
            width="160"
            height="200"
            viewBox="0 0 160 200"
            data-fr-js-ratio="true"
          >
            <use
              className="fr-artwork-motif"
              href={`${artworkOvoid}#artwork-motif`}
            ></use>
            <use
              className="fr-artwork-background"
              href={`${artworkOvoid}#artwork-background`}
            ></use>
            <g transform="translate(40, 60)">
              <use
                className="fr-artwork-decorative"
                href={`${pictogrammeUrl}#artwork-decorative`}
              ></use>
              <use
                className="fr-artwork-minor"
                href={`${pictogrammeUrl}#artwork-minor`}
              ></use>
              <use
                className="fr-artwork-major"
                href={`${pictogrammeUrl}#artwork-major`}
              ></use>
            </g>
          </svg>
        </div>
        <div className="fr-py-0 fr-col-12 fr-col-md-10 fr-col-offset-md-1">
          {action}
        </div>
      </div>
    </div>
  );
};
