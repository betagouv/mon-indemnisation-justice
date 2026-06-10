import { Loader } from "@/common/composants/Loader.tsx";
import { DocumentPDF } from "@/common/composants/document/DocumentPDF.tsx";
import { contenuFichier } from "@/common/services/fichier.ts";
import React, { useEffect, useState } from "react";

const fileReader = new FileReader();

export const PrevisualiserFichier = ({ fichier }: { fichier: File }) => {
  const [lectureEnCours, setLectureEnCours] = useState(false);
  const [urlFichier, setUrlFichier] = useState<string | undefined>(undefined);

  useEffect(() => {
    if (fichier) {
      setLectureEnCours(true);
      setUrlFichier(undefined);
      setUrlFichier(contenuFichier(fichier));
      setLectureEnCours(false);
    }
  }, [fichier]);

  return lectureEnCours ? (
    <Loader />
  ) : (
    <>
      {urlFichier && (
        <>
          {fichier.type == "application/pdf" && (
            <DocumentPDF url={urlFichier} />
          )}

          {fichier.type.match(/^image\/*/) && (
            <img
              src={urlFichier}
              alt={fichier.name}
              title={fichier.name}
              style={{
                width: "100%",
                maxHeight: "100vh",
                objectFit: "contain",
              }}
            />
          )}
        </>
      )}
    </>
  );
};
