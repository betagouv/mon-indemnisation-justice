import React, {useState,useEffect} from 'react';
import { Tabs } from "@codegouvfr/react-dsfr/Tabs";
const BrisPortes = function(props) {

  const [selectedTabId, setSelectedTabId] = useState("tab1");

  return (
    <Tabs
    tabs={[
        { label: "Tab 1", iconId: "fr-icon-add-line", content: <p>Content of tab1</p> },
        { label: "Tab 2", iconId: "fr-icon-ball-pen-fill", isDefault: true, content: <p>Content of tab2</p> },
        { label: "Tab 3", content: <p>Content of tab3</p> }
    ]}
/>
  );
}

export default BrisPortes;
