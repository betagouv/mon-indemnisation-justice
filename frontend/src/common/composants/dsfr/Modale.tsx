import { ModalProps } from "@codegouvfr/react-dsfr/Modal";
import { fr } from "@codegouvfr/react-dsfr/src/fr";
import { cx } from "@codegouvfr/react-dsfr/src/tools/cx.ts";
import React, { ForwardedRef, forwardRef, useImperativeHandle } from "react";

export type ModaleRef = {
  ouvrir: () => void;
  fermer: () => void;
};

export type ModaleProps = Omit<ModalProps, "size"> & {
  id: string;
  size?: "large" | "medium" | "small" | "full";
  onFerme?: () => void;
};

export const Modale = forwardRef<ModaleRef, ModaleProps>(
  (
    {
      children,
      concealingBackdrop,
      id,
      size = "large",
      onFerme,
      title,
      titleAs: TitleTag = "h1",
      iconId,
      titleProps,
      ...props
    }: ModaleProps,
    ref: ForwardedRef<ModaleRef>,
  ) => {
    useImperativeHandle(ref, () => ({
      ouvrir: () => window.dsfr(document.getElementById(id)).modal.disclose(),
      fermer: () => window.dsfr(document.getElementById(id)).modal.conceal(),
    }));

    return (
      <dialog
        aria-labelledby={`fr-modal-title-${id}`}
        id={id}
        className={cx(fr.cx("fr-modal"))}
        data-fr-concealing-backdrop={concealingBackdrop}
      >
        <div
          className={fr.cx(
            "fr-container",
            "fr-container--fluid",
            "fr-container-md",
          )}
        >
          <div className={fr.cx("fr-grid-row", "fr-grid-row--center")}>
            <div
              className={(() => {
                switch (size) {
                  case "full":
                    return fr.cx("fr-col-12");
                  case "large":
                    return fr.cx("fr-col-12", "fr-col-md-10", "fr-col-lg-8");
                  case "small":
                    return fr.cx("fr-col-12", "fr-col-md-6", "fr-col-lg-4");
                  case "medium":
                    return fr.cx("fr-col-12", "fr-col-md-8", "fr-col-lg-6");
                }
              })()}
            >
              <div className={fr.cx("fr-modal__body")}>
                <div className={fr.cx("fr-modal__header")}>
                  <button
                    className={fr.cx("fr-btn--close", "fr-btn")}
                    title="Fermer"
                    aria-controls={id}
                    type="button"
                    onClick={() => onFerme?.()}
                  >
                    Fermer
                  </button>
                </div>
                <div className={fr.cx("fr-modal__content")}>
                  <TitleTag
                    id={`fr-modal-title-${id}`}
                    {...titleProps}
                    className={cx(
                      titleProps?.className,
                      fr.cx("fr-modal__title"),
                    )}
                  >
                    {iconId !== undefined && (
                      <span
                        className={fr.cx(iconId, "fr-fi--lg", "fr-mr-1w")}
                        aria-hidden={true}
                      />
                    )}
                    {title}
                  </TitleTag>
                  {children}
                </div>
              </div>
            </div>
          </div>
        </div>
      </dialog>
    );
  },
);
