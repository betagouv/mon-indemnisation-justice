import React, {useEffect, useState} from 'react';
import {ToggleSwitch} from "@codegouvfr/react-dsfr/ToggleSwitch";
import {Button} from "@codegouvfr/react-dsfr/Button";

const Users = function ({items}) {

    const [isLoading, setIsLoading] = useState(false);
    const [data, setData] = useState([]);

    function handleCheck(id) {
        let copyData = [...data];
        copyData.map((item, index) => {
            if (item.id === id)
                copyData[index]['active'] = !item.active;

            const url = Routing.generate('_api_user_patch', {id: item.id});
            const data = {active: copyData[index]['active']};

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

        if (true === isLoading)
            return;
        setData(items);
        setIsLoading(true);
    }, [isLoading])

    return (
        <div className="fr-grid-row">
            <div className="fr-col-12">
                <div className="fr-table">
                    <div className="fr-table__wrapper">
                        <div className="fr-table__container">
                            <div className="fr-table__content">
                                <table>
                                    <caption>Administration des utilisateurs</caption>
                                    <thead>
                                    <tr>
                                        <th scope="col" className="fr-col-6">Adresse courriel</th>
                                        <th scope="col" className="fr-col-5">Rôle</th>
                                        <th scope="col" className="fr-col-1">Compte activé</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {data.map((item) => (
                                        <tr key={item.id}>
                                            <td className="fr-col-6">{item.email}</td>
                                            <td className="fr-col-5">{item.plaintextRole}</td>
                                            <td className="fr-col-1">
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

                    </div>
                </div>
            </div>
        </div>
    );
}

export default Users;
