<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity()]
#[ORM\Table(name: 'personnes')]
class Personne
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    protected ?Uuid $id = null;

    #[ORM\Column(type: 'string', length: 3, nullable: true, enumType: Civilite::class)]
    protected ?Civilite $civilite = null;


    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $prenom = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $nomNaissance = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $courriel = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $telephone = null;

    #[ORM\OneToOne(targetEntity: PersonnePhysique::class, mappedBy: 'personne', cascade: ['persist', 'remove'])]
    protected ?PersonnePhysique $personnePhysique = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getCivilite(): ?Civilite
    {
        return $this->civilite;
    }

    public function estFeminin(): bool
    {
        return $this->civilite->estFeminin();
    }

    public function setCivilite(?Civilite $civilite): self
    {
        $this->civilite = $civilite;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom, bool $deNaissance = false): self
    {
        $this->nom = $nom;

        if ($deNaissance && null === $this->nomNaissance) {
            $this->nomNaissance = $nom;
        }

        return $this;
    }

    public function getNomNaissance(): ?string
    {
        return $this->nomNaissance;
    }

    public function setNomNaissance(?string $nomNaissance): self
    {
        $this->nomNaissance = $nomNaissance;

        return $this;
    }

    /**
     * Retourne le nom prénom de la personne, éventuellement précédé par la civilité de la personne si $civilite et le
     * nom de famille en lettres capitales si $capitale.
     */
    public function getNomCourant(bool $civilite = false, bool $capital = false): string
    {
        return sprintf(
            '%s%s %s',
            $civilite ? ucfirst(strtolower($this->civilite->value)).'. ' : '',
            $this->prenom,
            $capital ? strtoupper($this->nom) : ucfirst($this->nom)
        );
    }

    public function getNomComplet(): string
    {
        $similarite = 0;
        if (null !== $this->nom && null !== $this->nomNaissance) {
            similar_text($this->nom, $this->nomNaissance, $similarite);
        }

        return sprintf(
            '%s %s %s %s',
            $this->civilite->getLibelle(),
            strtoupper($this->nom),
            empty($this->nomNaissance) || $similarite > 80 ? '' : $this->civilite->libelleNaissance($this->nomNaissance),
            $this->prenom
        );
    }

    public function __toString()
    {
        return implode(' ', [
            $this->civilite?->value,
            ucfirst(strtolower($this->prenom)),
            strtoupper($this->nomNaissance),
        ]);
    }

    public function getCourriel(): ?string
    {
        return $this->courriel;
    }

    public function setCourriel(?string $courriel): self
    {
        $this->courriel = $courriel;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getPersonnePhysique(): ?PersonnePhysique
    {
        return $this->personnePhysique;
    }

    public function setPersonnePhysique(PersonnePhysique $personnePhysique = new PersonnePhysique()): self
    {
        $this->personnePhysique = $personnePhysique->setPersonne($this);

        return $this;
    }
}
