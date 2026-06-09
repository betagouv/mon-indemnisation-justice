import workerSrc from "pdfjs-dist/build/pdf.worker.min.mjs?url";
import React, { useEffect, useRef, useState } from "react";
import { Document, Page, pdfjs } from "react-pdf";
import "react-pdf/dist/Page/AnnotationLayer.css";
import "react-pdf/dist/Page/TextLayer.css";

pdfjs.GlobalWorkerOptions.workerSrc = workerSrc;

const options = {
  cMapUrl: "/cmaps/",
  standardFontDataUrl: "/standard_fonts/",
};

export const DocumentPDF = ({ url }: { url: string }) => {
  const [nombreDePages, setNombreDePages] = useState<number>();
  const [numeroPage, setNumeroPage] = useState<number>(1);
  const [largeurConteneur, setLargeurConteneur] = useState<number>();
  const refConteneur = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (!refConteneur.current) return;
    const observateurTaille = new ResizeObserver(([entree]) => {
      setLargeurConteneur(entree.contentRect.width);
    });
    observateurTaille.observe(refConteneur.current);
    return () => observateurTaille.disconnect();
  }, []);

  return (
    <>
      {!!nombreDePages && (
        <div
          className="fr-grid-row fr-col-12"
          style={{
            display: "flex",
            justifyContent: "center",
            alignItems: "center",
            gap: "1rem",
          }}
        >
          <button
            type="button"
            className="fr-btn fr-btn--lg fr-btn--tertiary-no-outline fr-text--lg fr-text--bold"
            disabled={numeroPage <= 1}
            onClick={() => setNumeroPage((n) => Math.max(n - 1, 1))}
          >
            &lt;
          </button>
          <span>
            Page n° {numeroPage} / {nombreDePages}
          </span>
          <button
            type="button"
            className="fr-btn fr-btn--lg fr-btn--tertiary-no-outline fr-text--lg fr-text--bold"
            disabled={numeroPage >= nombreDePages}
            onClick={() =>
              setNumeroPage((n) => Math.min(n + 1, nombreDePages))
            }
          >
            &gt;
          </button>
        </div>
      )}
      <div className="fr-grid-row fr-col-12" ref={refConteneur}>
        <Document
          file={url}
          onLoadSuccess={({ numPages }) => {
            setNombreDePages(numPages);
            setNumeroPage(1);
          }}
          options={options}
        >
          <Page pageNumber={numeroPage} width={largeurConteneur} />
        </Document>
      </div>
    </>
  );
};
