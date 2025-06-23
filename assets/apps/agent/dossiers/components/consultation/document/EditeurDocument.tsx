import React from "react";
import { observer } from "mobx-react-lite";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";

export const EditeurDocument = observer(function EditeurDocumentComponent() {
  return (
    <>
      <div>
        <ButtonsGroup
          inlineLayoutWhen="always"
          alignment="right"
          buttonsIconPosition="right"
          buttons={[
            {
              children: "Éditer",
            },
            {
              children: "Visualiser",
            },
          ]}
        />
      </div>
    </>
  );
});
