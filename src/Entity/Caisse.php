<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CaisseRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: CaisseRepository::class)]

class Caisse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]

    #[Groups(["caisses","coutPrestations","prestations","operationCaisses", "stat","select_caisse","select_cout_prestations"])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["caisses","coutPrestations","prestations","operationCaisses", "stat", "select_caisse", "select_cout_prestations"])]
    private ?string $intitule = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["caisses","stat"])]
    private ?string $date = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["caisses", "stat", "select"])]
    private ?string $coutTotal = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["caisses","operationCaisses" ,"operabilite"])]
    private ?string $uuid = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]

    #[Groups(["caisses", "stat"])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["caisses"])]
    private ?\DateTimeInterface $updatedAt = null;


    #[ORM\ManyToOne(inversedBy: 'caisses')]
    #[Groups(["caisses", "operationCaisses" ,"operabilite"])]
    #[MaxDepth(1)]
    private ?User $utilisateursId = null;

    #[ORM\OneToMany(targetEntity: OperationCaisse::class, mappedBy: 'caisseId')]
    #[Groups(["caisses"])]
    #[MaxDepth(1)]

    private Collection $operationCaisses;

    #[ORM\ManyToOne(inversedBy: 'caisses')]
    #[Groups(["caisses", "operationCaisses", "journalCaisse"])]
    private ?Prestations $prestations = null;

    #[ORM\Column]
    #[Groups(["caisses"])]
    private ?bool $print = false;

    #[ORM\Column(unique: true, nullable: true)]
    #[Groups(["caisses","operationCaisses"])]
    private ?string $numero_recu_caisse;

    #[ORM\Column(unique: false, nullable: false, options: ['default' => '0'])]
    #[Groups(["caisses","operationCaisses"])]
    private ?string $status = '0';
    public function __construct()
    {
        $this->operationCaisses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getIntitule(): ?string
    {
        return $this->intitule;
    }

    public function setIntitule(?string $intitule): static
    {
        $this->intitule = $intitule;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(?string $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getCoutTotal(): ?string
    {
        return $this->coutTotal;
    }

    public function setCoutTotal(?string $coutTotal): static
    {
        $this->coutTotal = $coutTotal;

        return $this;
    }

    public function getUuid(): ?String
    {
        return $this->uuid;
    }

    public function setUuid(?String $uuid): static
    {
        $this->uuid = $uuid;

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



    public function getUtilisateursId(): ?User
    {
        return $this->utilisateursId;
    }

    public function setUtilisateursId(?User $utilisateursId): static
    {
        $this->utilisateursId = $utilisateursId;
        return $this;
    }

    /**
     * @return Collection<int, OperationCaisse>
     */
    public function getOperationCaisses(): Collection
    {
        return $this->operationCaisses;
    }

    public function addOperationCaiss(OperationCaisse $operationCaiss): static
    {
        if (!$this->operationCaisses->contains($operationCaiss)) {
            $this->operationCaisses->add($operationCaiss);
            $operationCaiss->setCaisseId($this);
        }

        return $this;
    }

    public function removeOperationCaiss(OperationCaisse $operationCaiss): static
    {
        if ($this->operationCaisses->removeElement($operationCaiss)) {
            // set the owning side to null (unless already changed)
            if ($operationCaiss->getCaisseId() === $this) {
                $operationCaiss->setCaisseId(null);
            }
        }
        return $this;
    }

    public function getPrestations(): ?Prestations
    {
        return $this->prestations;
    }

    public function setPrestations(?Prestations $prestations): static
    {
        $this->prestations = $prestations;

        return $this;
    }

    public function isPrint(): ?bool
    {
        return $this->print;
    }

    public function setPrint(bool $print): static
    {
        $this->print = $print;

        return $this;
    }

    public function getNumeroRecuCaisse(): ?string
    {
        return $this->numero_recu_caisse;
    }

    public function setNumeroRecuCaisse(?String $numero_recu_caisse): static
    {
        $this->numero_recu_caisse = $numero_recu_caisse;

        return $this;
    }
}
