import ReactQuill, { DeltaStatic, EmitterSource, Quill } from "react-quill-new";

import "react-quill-new/dist/quill.snow.css";

// Patch pour la gestion des ul/ol dans Quill
import List, { ListContainer } from "quill/formats/list";
import React from "react";
import UnprivilegedEditor = ReactQuill.UnprivilegedEditor;
import Range = ReactQuill.Range;

class UListContainer extends ListContainer {}
UListContainer.blotName = "ulist-container";
UListContainer.tagName = "UL";

class UListItem extends List {
  static register() {
    Quill.register(UListContainer);
  }
}

UListItem.blotName = "ulist";
UListItem.tagName = "LI";

UListContainer.allowedChildren = [UListItem];
UListItem.requiredContainer = UListContainer;

Quill.register({
  "formats/ordered": UListContainer,
  "formats/bullet": UListItem,
});

const QuillEditor = ({
  value,
  onChange,
  onMove,
  onBlur,
  readOnly = false,
}: {
  value: string;
  onChange?(value: string): void;
  onMove?(cursorIndex: number, selectionLength: number): void;
  onBlur?(): void;
  readOnly?: boolean;
}) => {
  return (
    <ReactQuill
      theme="snow"
      value={value}
      // Depuis react-quill-new 3.5, `onChange` renvoie par défaut `getSemanticHTML()`, qui avec Quill 2.0.3 remplace
      // chaque espace par `&nbsp;` : le texte ne revient alors plus à la ligne à l'impression du courrier. On récupère
      // donc le HTML brut de l'éditeur.
      useSemanticHTML={false}
      onChange={(
        value,
        delta: DeltaStatic,
        source: EmitterSource,
        editor: UnprivilegedEditor,
      ) => {
        if (source === "user") {
          onChange?.(value);
        }
      }}
      onChangeSelection={(selection: Range) => {
        if (selection) {
          onMove?.(selection.index, selection.length);
        }
      }}
      onBlur={() => onBlur?.()}
      readOnly={readOnly}
    />
  );
};

export { QuillEditor };
