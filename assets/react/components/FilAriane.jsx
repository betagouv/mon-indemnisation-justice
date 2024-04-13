import React,{useState, useEffect} from 'react';
import { Breadcrumb } from "@codegouvfr/react-dsfr/Breadcrumb";

const FilAriane = ({breadcrumb}) => {

  const [isLoading, setIsLoading] = useState(false);
  const [segments,setSegments]=useState([]);
  const [currentPageLabel,setCurrentPageLabel]=useState("");
  useEffect(() => {
    if(true === isLoading)
      return;
    const items     = breadcrumb.items;
    let tab         = [];
    const lastIndex = items.length-1;
    items.map((item,index) => {
      if(index < lastIndex)
        tab[index] = {label: item.label,linkProps: {href: item.url}};
      else
        setCurrentPageLabel(item.label);
    });
    setSegments(tab);
  },[isLoading]);

  return (
      <Breadcrumb
        segments={segments}
        currentPageLabel={currentPageLabel}
      />
  );
}

export default FilAriane;
