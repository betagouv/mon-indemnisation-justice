import { contenuFichier } from "@/common/services/fichier.ts";
import React, { useEffect, useState } from "react";
import { Loader } from "@/common/composants/Loader.tsx";

const fileReader = new FileReader();

export const PrevisualiserFichier = ({ fichier }: { fichier: File }) => {
  const [lectureEnCours, setLectureEnCours] = useState(false);
  const [urlFichier, setUrlFichier] = useState<string | undefined>(undefined);

  useEffect(() => {
    if (fichier) {
      setLectureEnCours(true);
      setUrlFichier(undefined);
      contenuFichier(fichier).then((urlLue) => {
        setLectureEnCours(false);
        setUrlFichier(urlLue);
      });
    }
  }, [fichier]);

  return lectureEnCours ? (
    <Loader />
  ) : (
    <>
      {fichier.type == "application/pdf" && (
        <object
          data={urlFichier}
          title={fichier.name}
          type="application/pdf"
          style={{
            width: "100%",
            aspectRatio: "210/297",
          }}
        ></object>
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
  );
};
