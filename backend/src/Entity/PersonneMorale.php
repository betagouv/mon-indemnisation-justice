<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Repository\PersonneMoraleRepository;

#[ORM\Table(name: 'personnes_morales')]
#[ORM\Entity(repositoryClass: PersonneMoraleRepository::class)]
class PersonneMorale
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(targetEntity: Dossier::class, mappedBy: 'requerantPersonneMorale')]
    /** @var Collection<Dossier> */
    protected Collection $dossiers;

    #[ORM\Column(length: 32, enumType: PersonneMoraleType::class, options: ['default' => PersonneMoraleType::ENTREPRISE_PRIVEE])]
    protected PersonneMoraleType $type;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sirenSiret = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $raisonSociale = null;

    #[ORM\ManyToOne(Adresse::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    protected ?Adresse $adresse = null;

    #[ORM\OneToOne(targetEntity: Personne::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'representant_legal_id', referencedColumnName: 'id')]
    protected ?Personne $representantLegal = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): PersonneMoraleType
    {
        return $this->type;
    }

    public function setType(PersonneMoraleType $type): PersonneMorale
    {
        $this->type = $type;

        return $this;
    }

    public function getSirenSiret(): ?string
    {
        return $this->sirenSiret;
    }

    public function setSirenSiret(?string $sirenSiret): PersonneMorale
    {
        $this->sirenSiret = $sirenSiret;

        return $this;
    }

    /**
     * @param bool|null $defini l'article, défini ou non, à utiliser.
     *
     * "Ex: l'assocation Machin" ou "un Syndic Truc"
     */
    public function getLibelle(?bool $defini): string
    {
        return sprintf('%s %s', $this->getTypeNettoye()->getLibelle($defini), $this->getRaisonSocialeNettoyee());
    }

    /**
     * Formate le libellé de la personne morale quand elle est préposée par "de".
     */
    public function getLibelleDe(): string
    {
        return sprintf('%s %s', $this->getTypeNettoye()->getLibelleDe(), $this->getRaisonSocialeNettoyee());
    }

    /**
     * Essaye de détecter une erreur de type en fonction du nom de la personne morale.
     *
     * Notamment si la raison sociale commence par "SCI", alors le type est SCI.
     */
    protected function getTypeNettoye(): PersonneMoraleType
    {
        return preg_match('/\s*sci/i', $this->raisonSociale) ? PersonneMoraleType::SCI : $this->type;
    }

    /**
     * Essaye de détecter une erreur de type en fonction du nom de la personne morale.
     *
     * Notamment si la raison sociale commence par "SCI", alors le type est SCI.
     */
    protected function getRaisonSocialeNettoyee(): string
    {
        return preg_match('/^\s*sci\s*/i', $this->raisonSociale) ? preg_replace('/\s*sci\s*/i', '', $this->raisonSociale) : $this->raisonSociale;
    }

    public function getRaisonSociale(): ?string
    {
        return $this->raisonSociale;
    }

    public function setRaisonSociale(?string $raisonSociale): PersonneMorale
    {
        $this->raisonSociale = $raisonSociale;

        return $this;
    }

    public function getAdresse(): ?Adresse
    {
        return $this->adresse;
    }

    public function setAdresse(?Adresse $adresse): PersonneMorale
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getRepresentantLegal(): ?Personne
    {
        return $this->representantLegal;
    }

    public function setRepresentantLegal(?Personne $representantLegal): PersonneMorale
    {
        $this->representantLegal = $representantLegal;

        return $this;
    }
}
