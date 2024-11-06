<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241031135437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Créer les tables geo département et région  ';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE geo_departements (code VARCHAR(3) NOT NULL, region_code VARCHAR(2) NOT NULL, nom VARCHAR(32) NOT NULL, est_deploye BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(code))');
        $this->addSql('CREATE INDEX IDX_19A85389AEB327AF ON geo_departements (region_code)');
        $this->addSql('CREATE TABLE geo_regions (code VARCHAR(2) NOT NULL, nom VARCHAR(32) NOT NULL, PRIMARY KEY(code))');
        $this->addSql('ALTER TABLE geo_departements ADD CONSTRAINT FK_19A85389AEB327AF FOREIGN KEY (region_code) REFERENCES geo_regions (code) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql(<<<SQL
insert into geo_regions (code, nom) values ('11','Île-de-France'),
('93','Provence-Alpes-Côte d''Azur'),
('52','Pays de la Loire'),
('76','Occitanie'),
('75','Nouvelle-Aquitaine'),
('28','Normandie'),
('06','Mayotte'),
('02','Martinique'),
('04','La Réunion'),
('32','Hauts-de-France'),
('03','Guyane'),
('01','Guadeloupe'),
('44','Grand Est'),
('94','Corse'),
('24','Centre-Val de Loire'),
('53','Bretagne'),
('27','Bourgogne-Franche-Comté'),
('84','Auvergne-Rhône-Alpes')
SQL
        );
        $this->addSql(<<<SQL
insert into geo_departements (code, nom, region_code) values ('75','Paris','11'),
('77','Seine-et-Marne','11'),
('78','Yvelines','11'),
('91','Essonne','11'),
('92','Hauts-de-Seine','11'),
('93','Seine-Saint-Denis','11'),
('94','Val-de-Marne','11'),
('95','Val-d''Oise','11'),
('04','Alpes-de-Haute-Provence','93'),
('05','Hautes-Alpes','93'),
('06','Alpes-Maritimes','93'),
('13','Bouches-du-Rhône','93'),
('83','Var','93'),
('84','Vaucluse','93'),
('44','Loire-Atlantique','52'),
('49','Maine-et-Loire','52'),
('53','Mayenne','52'),
('72','Sarthe','52'),
('85','Vendée','52'),
('09','Ariège','76'),
('11','Aude','76'),
('12','Aveyron','76'),
('30','Gard','76'),
('31','Haute-Garonne','76'),
('32','Gers','76'),
('34','Hérault','76'),
('46','Lot','76'),
('48','Lozère','76'),
('65','Hautes-Pyrénées','76'),
('66','Pyrénées-Orientales','76'),
('81','Tarn','76'),
('82','Tarn-et-Garonne','76'),
('16','Charente','75'),
('17','Charente-Maritime','75'),
('19','Corrèze','75'),
('23','Creuse','75'),
('24','Dordogne','75'),
('33','Gironde','75'),
('40','Landes','75'),
('47','Lot-et-Garonne','75'),
('64','Pyrénées-Atlantiques','75'),
('79','Deux-Sèvres','75'),
('86','Vienne','75'),
('87','Haute-Vienne','75'),
('14','Calvados','28'),
('27','Eure','28'),
('50','Manche','28'),
('61','Orne','28'),
('76','Seine-Maritime','28'),
('976','Mayotte','06'),
('972','Martinique','02'),
('974','La Réunion','04'),
('02','Aisne','32'),
('59','Nord','32'),
('60','Oise','32'),
('62','Pas-de-Calais','32'),
('80','Somme','32'),
('973','Guyane','03'),
('971','Guadeloupe','01'),
('08','Ardennes','44'),
('10','Aube','44'),
('51','Marne','44'),
('52','Haute-Marne','44'),
('54','Meurthe-et-Moselle','44'),
('55','Meuse','44'),
('57','Moselle','44'),
('67','Bas-Rhin','44'),
('68','Haut-Rhin','44'),
('88','Vosges','44'),
('2A','Corse-du-Sud','94'),
('2B','Haute-Corse','94'),
('18','Cher','24'),
('28','Eure-et-Loir','24'),
('36','Indre','24'),
('37','Indre-et-Loire','24'),
('41','Loir-et-Cher','24'),
('45','Loiret','24'),
('22','Côtes-d''Armor','53'),
('29','Finistère','53'),
('35','Ille-et-Vilaine','53'),
('56','Morbihan','53'),
('21','Côte-d''Or','27'),
('25','Doubs','27'),
('39','Jura','27'),
('58','Nièvre','27'),
('70','Haute-Saône','27'),
('71','Saône-et-Loire','27'),
('89','Yonne','27'),
('90','Territoire de Belfort','27'),
('01','Ain','84'),
('03','Allier','84'),
('07','Ardèche','84'),
('15','Cantal','84'),
('26','Drôme','84'),
('38','Isère','84'),
('42','Loire','84'),
('43','Haute-Loire','84'),
('63','Puy-de-Dôme','84'),
('69','Rhône','84'),
('73','Savoie','84'),
('74','Haute-Savoie','84')
SQL
        );

        // Activer les départements
        $this->addSql("update geo_departements set est_deploye = true where code in ('13', '33', '35', '59', '67', '69', '77', '83')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE geo_departements DROP CONSTRAINT FK_19A85389AEB327AF');
        $this->addSql('DROP TABLE geo_departements');
        $this->addSql('DROP TABLE geo_regions');
    }
}



