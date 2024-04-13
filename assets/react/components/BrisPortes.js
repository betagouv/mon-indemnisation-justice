import React, {useState,useEffect} from 'react';
import { Tabs } from "@codegouvfr/react-dsfr/Tabs";
import { Accordion } from "@codegouvfr/react-dsfr/Accordion";
import { fr } from "@codegouvfr/react-dsfr";

const BrisPortes = function(props) {

  return (
    <>
      <Tabs
        tabs={[
            { label: "Tab 1", iconId: "fr-icon-add-line", content: <p>Content of tab1</p> },
            { label: "Tab 2", iconId: "fr-icon-ball-pen-fill", content: <p>Content of tab2</p> },
            { label: "Tab 3", content: <p>Content of tab3</p> }
        ]}
      />
      <div className={fr.cx("fr-accordions-group")}>
          <Accordion label="Name of the Accordion 1"><p>Content of the Accordion 1</p></Accordion>
          <Accordion label="Name of the Accordion 2">Content of the Accordion 2</Accordion>
      </div>
    </>
  );
}

export default BrisPortes;
