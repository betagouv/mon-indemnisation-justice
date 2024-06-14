import React from 'react';

const ServiceNational = () => {
  return (
    <section className="pr-service-national">
        <div className="fr-p-4w">
          <div className="pr-service-national_body fr-p-4w">
            <div className="fr-grid-row fr-grid-row--gutters fr-grid-row--middle">
              <div className="fr-col-5">
                <div className="pr-service-national-illu_france">
                  <span className="fr-sr-only">Illustration des territoires français</span>
                </div>
              </div>
              <div className="fr-col-7">
                <div className="pr-service-national-text fr-ml-6w">
                  <h3>Service national</h3>
                  <p>
                    Nous répondons aux demandes des justiciables de l'ensemble
                    du territoire national.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
  );
}

export default ServiceNational;
