<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PartenairesRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PartenairesRepository::class)]
class Partenaires
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["stat", "caisses","coutPrestations","partenaires","operationCaisses", "select_partenaires", "users"])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["stat", "caisses","users","partenaires","coutPrestations","operationCaisses", "select_partenaires"])]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["stat",   "caisses","partenaires"])]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["stat", "caisses","partenaires"])]
    private ?string $responsable = null;

    #[ORM\Column]
    #[Groups(["stat", "caisses", "partenaires"])]
    private ?string $telResponsable = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["partenaires"])]
    private ?string $uuid = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([ "caisses", "partenaires"])]
    private ?string $admin = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(targetEntity: CoutPrestations::class, mappedBy: 'partenairesId')]

    private Collection $coutPrestations;

    #[ORM\OneToMany(targetEntity: OperationCaisse::class, mappedBy: 'partenairesId')]
    #[Groups([ "caisses"])]
    private Collection $operationCaisses;


    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'id_partenaire')]
    private Collection $users;

    public function __construct()
    {
        $this->coutPrestations = new ArrayCollection();
        $this->operationCaisses = new ArrayCollection();
        $this->users = new ArrayCollection();
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

    public function getResponsable(): ?string
    {
        return $this->responsable;
    }

    public function setResponsable(?string $responsable): static
    {
        $this->responsable = $responsable;

        return $this;
    }

    public function getTelResponsable(): ?string
    {
        return $this->telResponsable;
    }

    public function setTelResponsable(string $telResponsable): static
    {
        $this->telResponsable = $telResponsable;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getAdmin(): ?string
    {
        return $this->admin;
    }

    public function setAdmin(?string $admin): static
    {
        $this->admin = $admin;

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
            $coutPrestation->setPartenairesId($this);
        }

        return $this;
    }

    public function removeCoutPrestation(CoutPrestations $coutPrestation): static
    {
        if ($this->coutPrestations->removeElement($coutPrestation)) {
            // set the owning side to null (unless already changed)
            if ($coutPrestation->getPartenairesId() === $this) {
                $coutPrestation->setPartenairesId(null);
            }
        }

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
            $operationCaiss->setPartenairesId($this);
        }

        return $this;
    }

    public function removeOperationCaiss(OperationCaisse $operationCaiss): static
    {
        if ($this->operationCaisses->removeElement($operationCaiss)) {
            // set the owning side to null (unless already changed)
            if ($operationCaiss->getPartenairesId() === $this) {
                $operationCaiss->setPartenairesId(null);
            }
        }

        return $this;
    }

    // public function getUser(): ?User
    // {
    //     return $this->user;
    // }

    // public function setUser(?User $user): static
    // {
    //     // unset the owning side of the relation if necessary
    //     if ($user === null && $this->user !== null) {
    //         $this->user->setPartenairesId(null);
    //     }

    //     // set the owning side of the relation if necessary
    //     if ($user !== null && $user->getPartenairesId() !== $this) {
    //         $user->setPartenairesId($this);
    //     }

    //     $this->user = $user;

    //     return $this;
    // }

    /**
     * @return Collection<int, User>
     */
    // public function getUsers(): Collection
    // {
    //     return $this->users;
    // }

    // public function addUser(User $user): static
    // {
    //     if (!$this->users->contains($user)) {
    //         $this->users->add($user);
    //         $user->setIdPartenaire($this);
    //     }

    //     return $this;
    // }

    // public function removeUser(User $user): static
    // {
    //     if ($this->users->removeElement($user)) {
    //         // set the owning side to null (unless already changed)
    //         if ($user->getIdPartenaire() === $this) {
    //             $user->setIdPartenaire(null);
    //         }
    //     }

    //     return $this;
    // }
}
