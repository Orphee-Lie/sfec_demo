<?php

namespace App\Entity;

use App\Entity\Caisse;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PrestationsRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: PrestationsRepository::class)]
class Prestations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["stat","caisses","coutPrestations","prestations","select_prestation","select_prestation_cout","operationCaisses"])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["stat", "caisses", "coutPrestations","prestations","select_prestation","operationCaisses","journalCaisse"])]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["stat","prestations"])]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["stat","prestations","select_prestation_cout"])]
    private ?int $coutTotal = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["stat","prestations"])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(targetEntity: CoutPrestations::class, mappedBy: 'prestationsId')]
    // #[Groups(["operationCaisses", "select_prestation"])]
    private Collection $coutPrestations;

    #[ORM\OneToMany(targetEntity: Caisse::class, mappedBy: 'prestations')]
    private Collection $caisses;

    // #[ORM\ManyToOne(inversedBy: 'prestationsId')]
    // #[Groups([ "prestations"])]
    // private ?Caisse $caisse = null;


    public function __construct()
    {
        $this->coutPrestations = new ArrayCollection();
        $this->caisses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCoutTotal(): ?int
    {
        return $this->coutTotal;
    }

    public function setCoutTotal(?int $coutTotal): static
    {
        $this->coutTotal = $coutTotal;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, CoutPrestations>
     */
    public function getCoutPrestations(): Collection
    {
        return $this->coutPrestations;
    }

    public function addCoutPrestation(CoutPrestations $coutPrestation): static
    {
        if (!$this->coutPrestations->contains($coutPrestation)) {
            $this->coutPrestations->add($coutPrestation);
            $coutPrestation->setPrestationsId($this);
        }

        return $this;
    }

    public function removeCoutPrestation(CoutPrestations $coutPrestation): static
    {
        if ($this->coutPrestations->removeElement($coutPrestation)) {
            // set the owning side to null (unless already changed)
            if ($coutPrestation->getPrestationsId() === $this) {
                $coutPrestation->setPrestationsId(null);
            }
        }

        return $this;
    }

    // public function getCaisse(): ?Caisse
    // {
    //     return $this->caisse;
    // }

    // public function setCaisse(?Caisse $caisse): static
    // {
    //     $this->caisse = $caisse;

    //     return $this;
    // }

    /**
     * @return Collection<int, Caisse>
     */
    public function getCaisses(): Collection
    {
        return $this->caisses;
    }

    public function addCaiss(Caisse $caiss): static
    {
        if (!$this->caisses->contains($caiss)) {
            $this->caisses->add($caiss);
            $caiss->setPrestations($this);
        }

        return $this;
    }

    public function removeCaiss(Caisse $caiss): static
    {
        if ($this->caisses->removeElement($caiss)) {
            // set the owning side to null (unless already changed)
            if ($caiss->getPrestations() === $this) {
                $caiss->setPrestations(null);
            }
        }

        return $this;
    }
}
