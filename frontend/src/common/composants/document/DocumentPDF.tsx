import React, { useState } from "react";
import { Document, Page, pdfjs } from "react-pdf";
import "react-pdf/dist/Page/AnnotationLayer.css";
import "react-pdf/dist/Page/TextLayer.css";
import workerSrc from "pdfjs-dist/build/pdf.worker.min.mjs?url";

pdfjs.GlobalWorkerOptions.workerSrc = workerSrc;

const options = {
  cMapUrl: "/cmaps/",
  standardFontDataUrl: "/standard_fonts/",
};

export const DocumentPDF = ({ url }: { url: string }) => {
  const [nombreDePages, setNombreDePages] = useState<number>();
  const [numeroPage, setNumeroPage] = useState<number>(1);

  return (
    <>
      <Document
        file={url}
        onLoadSuccess={({ numPages }) => {
          setNombreDePages(numPages);
          setNumeroPage(1);
        }}
        options={options}
      >
        <Page pageNumber={numeroPage} width={Math.min(window.innerWidth, 800)} />
      </Document>
      {!!nombreDePages && (
        <div>
          <p>
            Page {numeroPage} / {nombreDePages}
          </p>
          <button
            type="button"
            disabled={numeroPage <= 1}
            onClick={() =>
              setNumeroPage((n) => Math.max(n - 1, 1))
            }
          >
            Page précédente
          </button>
          <button
            type="button"
            disabled={numeroPage >= nombreDePages}
            onClick={() =>
              setNumeroPage((n) => Math.min(n + 1, nombreDePages))
            }
          >
            Page suivante
          </button>
        </div>
      )}
    </>
  );
};
