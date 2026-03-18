import classes from "@/apps/requerant/style/composants/IconeLigne.module.css";
import type {
  FrIconClassName,
  RiIconClassName,
} from "@codegouvfr/react-dsfr/src/fr/generatedFromCss/classNames.ts";
import React from "react";

export const IconeLigne = ({
  iconId,
  className,
}: {
  iconId: FrIconClassName | RiIconClassName;
  className?: string;
}) => (
  <span
    className={`${iconId} ${classes.iconeLigne} ${className || ""}`}
    aria-hidden="true"
  ></span>
);
