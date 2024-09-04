import React from 'react';
import CategorieDemandeCard from './CategorieDemandeCard';

const CategorieDemandes = function() {
  return (
    <section className="pr-requests">
      <div className="fr-px-4w fr-pt-4w">
        <h2>Catégories de demandes</h2>
        <p>Vous avez la possibilité d'effectuer un test d'éligibilité suivi d'une demande amiable d'indemnisation en sélectionnant l'une des catégories suivantes.</p>
        <ul className="fr-grid-row fr-grid-row--gutters">
          <li className="fr-col-12 fr-col-lg-4">
            <CategorieDemandeCard
              title="Bris de porte"
              chapo="Ou autres dégâts matériels causés dans le cadre d'une opération de police judiciaire"
              enabled={true}
              href={Routing.generate('home_test_eligibilite_bris_porte')}
            />
          </li>
          <li className="fr-col-12 fr-col-lg-4">
            <CategorieDemandeCard
              title="Perte de loyers"
              chapo="En raison du placement sous scellés d'un bien immobilier pour les besoins d'une enquête judiciaire"
              enabled={false}
            />
          </li>
          <li className="fr-col-12 fr-col-lg-4">
            <CategorieDemandeCard
              title="Dysfonctionnements du service public de la justice"
              chapo="Délai de procédure déraisonnable devant les juridictions de l'ordre judiciaire, non restitution des scellés, mise en fourrière injustifiée, ..."
              enabled={false}
            />
          </li>
          <li className="fr-col-12 fr-col-lg-6">
            <CategorieDemandeCard
              title="Accident impliquant un véhicule de la Direction des services judiciaires"
              chapo=""
              enabled={false}
              vertical={false}
            />
          </li>
          <li className="fr-col-12 fr-col-lg-6">
            <CategorieDemandeCard
              title="Sinistre survenu au sein d’un bâtiment judiciaire"
              chapo=""
              enabled={false}
              vertical={false}
            />
          </li>
        </ul>
      </div>
    </section>
  );
}

export default CategorieDemandes;
