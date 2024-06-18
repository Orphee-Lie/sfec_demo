<?php

namespace App\Entity;

use App\Entity\Prestations;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use App\Repository\CoutPrestationsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: CoutPrestationsRepository::class)]

class CoutPrestations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["stat", "caisses","coutPrestations","prestations","operationCaisses","select_cout_prestations"])]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["stat", "caisses","coutPrestations","prestations","operationCaisses","select_cout_prestations"])]
    private ?int $cout = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["coutPrestations"])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]

    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'coutPrestations')]
    #[Groups(["caisses","coutPrestations"])]
    #[MaxDepth(1)]
    private ?Partenaires $partenairesId = null;

    #[ORM\ManyToOne(inversedBy: 'coutPrestations')]
    #[Groups(["caisses","stat", "coutPrestations"])]
    #[MaxDepth(1)]
    private ?Prestations $prestationsId = null;

    #[ORM\OneToMany(targetEntity: OperationCaisse::class, mappedBy: 'coutPrestationsId')]
    #[Groups(["stat"])]
    #[MaxDepth(1)]
    private Collection $operationCaisses;

    public function __construct()
    {
        $this->operationCaisses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCout(): ?int
    {
        return $this->cout;
    }

    public function setCout(?int $cout): static
    {
        $this->cout = $cout;

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

    public function getPartenairesId(): ?Partenaires
    {
        return $this->partenairesId;
    }

    public function setPartenairesId(?Partenaires $partenairesId): static
    {
        $this->partenairesId = $partenairesId;

        return $this;
    }

    public function getPrestationsId(): ?Prestations
    {
        return $this->prestationsId;
    }

    public function setPrestationsId(?Prestations $PrestationsId): static
    {
        $this->prestationsId = $PrestationsId;

        return $this;
    }


    /**
     * @return Collection<int, OperationCaisse>
     */
    public function getOperationCaisses(): Collection
    {
        return $this->operationCaisses;
    }

    // public function addOperationCaiss(OperationCaisse $operationCaiss): static
    // {
    //     if (!$this->operationCaisses->contains($operationCaiss)) {
    //         $this->operationCaisses->add($operationCaiss);
    //         $operationCaiss->setCoutPrestationsId($this);
    //     }

    //     return $this;
    // }

    // public function removeOperationCaiss(OperationCaisse $operationCaiss): static
    // {
    //     if ($this->operationCaisses->removeElement($operationCaiss)) {
    //         // set the owning side to null (unless already changed)
    //         if ($operationCaiss->getCoutPrestationsId() === $this) {
    //             $operationCaiss->setCoutPrestationsId(null);
    //         }
    //     }

    //     return $this;
    // }
}
