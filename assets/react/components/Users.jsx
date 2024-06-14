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
  const headers = [
    trans(LOGIN_EMAIL),
    trans(USER_FIELD_ROLE),
    trans(USER_FIELD_ACTIVE)
  ];

  function handleCheck(id) {
    let copyData = [...data];
    copyData.map((item,index) => {
      if(item.id === id)
        copyData[index]['active']=!item.active;

      const url =Routing.generate('_api_user_patch',{id:item.id});
      const data = { active: copyData[index]['active'] };

      fetch(url, {
        method: 'PATCH',
        headers: {'Content-Type': 'application/merge-patch+json'},
        body: JSON.stringify(data)
      })
      .then((response) => response.json())
      .then((data) => console.log('backup user'))
      ;
    });
    setData(copyData);
  }

  useEffect(() => {

    if(true===isLoading)
      return;
    setData(items);
    setIsLoading(true);
  },[isLoading])

  return (
    <div className="fr-grid-row">
      <div className="fr-col-12">
        <div className="fr-table">
          <table>
            <caption>{trans(ADMIN_HOMEPAGE_TITLE)}</caption>
            <thead>
              <tr>
                <th scope="col">{trans(LOGIN_EMAIL)}</th>
                <th scope="col">{trans(USER_FIELD_ROLE)}</th>
                <th scope="col">{trans(USER_FIELD_ACTIVE)}</th>
              </tr>
            </thead>
            <tbody>
            {data.map((item) => (
              <tr key={item.id}>
                <td>{item.email}</td>
                <td>{item.plaintextRole}</td>
                <td>
                  <ToggleSwitch
                      checked={item.active}
                      onChange={() => handleCheck(item.id)}
                  />
                </td>
              </tr>)
            )}
            </tbody>
          </table>
        </div>
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
