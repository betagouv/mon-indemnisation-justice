import React, {useReducer} from 'react';
import ReactDOM from "react-dom/client";
import BrisPortePanel from '@/react/components/BrisPortePanel.jsx';
import { DossierContext, PatchDossierContext } from '@/react/contexts/DossierContext.ts';
import _ from "lodash";

// En développement, vider la console après chaque action de HMR (Hot Module Replacement)
if (import.meta.hot) {
  import.meta.hot.on(
    "vite:beforeUpdate",
    () => console.clear()
  );
}

const { dossier, router } = JSON.parse(document.getElementById('react-arguments').textContent);

const root = ReactDOM.createRoot(document.getElementById('react-app'));

let queuedChanges = {};


const apiPatch = () => {
    if (Object.keys(queuedChanges).length > 0) {
        // Run a PATCH call and store the result as state
        fetch(`/api/requerant/dossier/${dossier.id}`, {
            method: 'PATCH',
            redirect: 'error',
            headers: {'Content-Type': 'application/merge-patch+json'},
            body: JSON.stringify(queuedChanges)
        }).then(
            (response) => {
                queuedChanges = {};
            }
        )
    }
}

const debouncedApiPatch = _.debounce(apiPatch, 1000);


function DossierApp({dossier}) {
    const [_dossier, _patchDossier] = useReducer((dossier: object, changes: any) => {
        const { patch = true, ...diff } = changes;

        // Ugly patch to avoid recursive merge to mess data with arrays
        if (Array.isArray(diff?.liasseDocumentaire?.documents)) {
            return {
                ...dossier,
                ...{
                    liasseDocumentaire: {
                        documents: diff?.liasseDocumentaire?.documents
                    }
                }
            };
        }
        if (Array.isArray(diff?.requerant?.personnePhysique?.liasseDocumentaire?.documents)) {
            return {
                ...dossier,
                ...{
                    requerant: {
                        personnePhysique: {
                            liasseDocumentaire: {
                                documents: diff?.requerant?.personnePhysique?.liasseDocumentaire?.documents
                            }
                        }

                    }
                }
            };
        }
        if (Array.isArray(diff?.requerant?.personneMorale?.liasseDocumentaire?.documents)) {
            return {
                ...dossier,
                ...{
                    requerant: {
                        personneMorale: {
                            liasseDocumentaire: {
                                documents: diff?.requerant?.personneMorale?.liasseDocumentaire?.documents
                            }
                        }

                    }
                }
            };
        }

        if (patch) {
            // Ajouter les changements à la file d'attente
            queuedChanges = {
                ..._.merge(
                    queuedChanges, diff
                )
            };
            debouncedApiPatch();
        }

        // Il faut recréer un objet pour que le re-render soit déclenché
        return {
            ..._.merge(
                dossier, diff
            )
        };
    }, dossier);

    return (
        <DossierContext.Provider value={_dossier} >
            <PatchDossierContext.Provider value={_patchDossier} >
                <div className="fr-container">
                    <h1>Déclarer un bris de porte</h1>
                    <BrisPortePanel routes={ router }/>
                </div>
            </PatchDossierContext.Provider>
        </DossierContext.Provider>
    )
}

root.render(
    <React.StrictMode>
        <>
            <DossierApp dossier={dossier} />
        </>
    </React.StrictMode>
);