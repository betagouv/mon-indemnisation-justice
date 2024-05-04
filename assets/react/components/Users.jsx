import React, {useState,useEffect} from 'react';
import { fr } from "@codegouvfr/react-dsfr";
import { Table } from "@codegouvfr/react-dsfr/Table";
import { ToggleSwitch } from "@codegouvfr/react-dsfr/ToggleSwitch";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { format } from "date-fns";
import {
  trans,
  ADMIN_HOMEPAGE_TITLE,
  LOGIN_EMAIL,
  USER_FIELD_ROLE,
  USER_FIELD_ACTIVE,
  GLOBAL_ACTIONS,
  GLOBAL_BTN_UPDATE
} from '../../translator';
const Users = function({items}) {

  const [isLoading, setIsLoading]=useState(false);

  const [data,setData] = useState([]);
  const [_items,setItems]=useState(items);

  const headers = [
    trans(LOGIN_EMAIL),
    trans(USER_FIELD_ROLE),
    trans(USER_FIELD_ACTIVE)
  ];

  function handleActive(checked,index) {
    console.log(index);
  }

  useEffect(() => {

    if(true===isLoading)
      return;

    let tmp=[];
    items.map((item) => {
      let index = tmp.length;
      tmp[index]=[
        item.email,
        item.plaintextRole,
        <ToggleSwitch
            checked={_items[index]}
            onChange={checked => handleActive(checked,index)}
        />,
      ];
    });

    setData(tmp);
    setIsLoading(true);
  },[isLoading,_items])

  return (
    <div className="fr-grid-row">
      <div className="fr-col-12">
        <Table
          caption={trans(ADMIN_HOMEPAGE_TITLE)}
          data={data}
          headers={headers}
          fixed
        />
      </div>
      <div className="fr-col-9">
      </div>
      <div className="fr-col-3">
        <Button
        linkProps={{
          href: '#'
        }}
        >{"@todo"}
        </Button>
      </div>
    </div>
  );
}

export default Users;
