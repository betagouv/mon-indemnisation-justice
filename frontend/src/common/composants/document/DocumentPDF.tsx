import workerSrc from "pdfjs-dist/legacy/build/pdf.worker.min.mjs?url";
import React, { useEffect, useMemo, useRef, useState } from "react";
import { Document, Page, pdfjs } from "react-pdf";
import "react-pdf/dist/Page/AnnotationLayer.css";
import "react-pdf/dist/Page/TextLayer.css";

pdfjs.GlobalWorkerOptions.workerSrc = workerSrc;

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

  const options = useMemo(
    () => ({
      cMapUrl: "/cmaps/",
      standardFontDataUrl: "/standard_fonts/",
    }),
    [1],
  );

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
            onClick={() => setNumeroPage((n) => Math.min(n + 1, nombreDePages))}
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
          {Array.from(Array(nombreDePages).keys()).map((index) => (
            <Page
              key={`page-${index + 1}`}
              pageNumber={index + 1}
              width={index + 1 === numeroPage ? largeurConteneur : undefined}
              renderMode={index + 1 === numeroPage ? "canvas" : "none"}
            />
          ))}
        </Document>
      </div>
    </>
  );
};
