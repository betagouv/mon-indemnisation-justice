import {TableProps} from "@codegouvfr/react-dsfr/Table";
import {fr} from "@codegouvfr/react-dsfr/src/fr";
import {cx} from "@codegouvfr/react-dsfr/src/tools/cx.ts";
import {useAnalyticsId} from "@codegouvfr/react-dsfr/src/tools/useAnalyticsId.ts";
import React, {type CSSProperties, forwardRef, type ReactNode} from "react";
import type {Equals} from "tsafe";
import {assert} from "tsafe/assert";

type CelluleTableau =
  | ReactNode
  | {
      className?: string;
      style?: CSSProperties;
      children: ReactNode;
    };

export type TableauProps = Omit<TableProps, "headers" | "data"> & {
  headers?: CelluleTableau[];
  data: CelluleTableau[][];
};

const extraireCelluleProps = (cellule: CelluleTableau) => {
  return typeof cellule === "object" &&
    cellule !== null &&
    "children" in cellule
    ? {
        children: cellule.children,
        className: "className" in cellule ? cellule.className : "",
        style: "style" in cellule ? cellule.style : {},
      }
    : { children: cellule, className: "", style: {} };
};

export const Tableau = forwardRef<HTMLDivElement, TableauProps>(
  (props, ref) => {
    const {
      id: id_props,
      data,
      headers,
      caption,
      bordered = false,
      noScroll = false,
      fixed = false,
      noCaption = false,
      bottomCaption = false,
      colorVariant,
      className,
      style,
      ...rest
    } = props;
    // Ré-écriture du composant parent /!\ mettre à jour si jamais le composant évolue
    assert<Equals<keyof typeof rest, never>>();

    const id = useAnalyticsId({
      defaultIdPrefix: "fr-table",
      explicitlyProvidedId: id_props,
    });

    return (
      <div
        id={id}
        ref={ref}
        style={style}
        className={cx(
          fr.cx(
            "fr-table",
            {
              "fr-table--bordered": bordered,
              "fr-table--no-scroll": noScroll,
              "fr-table--layout-fixed": fixed,
              "fr-table--no-caption": noCaption,
              "fr-table--caption-bottom": bottomCaption,
            },
            colorVariant !== undefined && `fr-table--${colorVariant}`,
          ),
          className,
        )}
      >
        <table>
          {caption !== undefined && <caption>{caption}</caption>}
          {headers !== undefined && (
            <thead>
              <tr>
                {headers.map((header: CelluleTableau, i: number) => {
                  const { children, className, style } =
                    extraireCelluleProps(header);
                  return (
                    <th key={i} scope="col" className={className} style={style}>
                      {children}
                    </th>
                  );
                })}
              </tr>
            </thead>
          )}
          <tbody>
            {data.map((row, i) => (
              <tr key={i}>
                {row.map((col, j) => {
                  const { children, className, style } =
                    extraireCelluleProps(col);

                  return (
                    <td key={j} className={className} style={style}>
                      {children}
                    </td>
                  );
                })}
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    );
  },
);
