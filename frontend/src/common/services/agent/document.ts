import {Document} from "@/common/models";
import {plainToInstance} from "class-transformer";

interface DocumentManagerInterface {
    imprimer(document: Document, corps: string): Promise<Document>;
}

class APIDocumentManager implements DocumentManagerInterface {
    async imprimer(document: Document, corps: string): Promise<Document> {
        return new Promise(async (resolve, reject) => {
            const response = await fetch(
                `/api/agent/fip6/document/${document.id}/imprimer`,
                {
                    headers: {
                        "Content-type": "application/json",
                    },
                    method: "PUT",
                    body: JSON.stringify({corps}),
                },
            );

            if (response.ok) {
                const data = await response.json();

                resolve(plainToInstance(Document, data));
            } else {
                reject("Une erreur est survenue lors de l'appel à l'API");
            }
        });
    }
}

export {type DocumentManagerInterface, APIDocumentManager};
