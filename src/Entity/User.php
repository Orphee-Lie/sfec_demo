<?php

namespace App\Entity;

use DateTime;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["statistiques", "caisses", "partenaires", "prestations", "coutPrestations", "users", "select_utilisateurs","operationCaisses"])]
    private ?int $id = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(["statistiques", "users"])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Groups(["statistiques","users"])]
    private ?string $password = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[Groups(["statistiques", "users", "operationCaisses", "operabilite"])]
    private ?Site $id_site = null;

    #[ORM\Column(length: 255)]
    #[Groups(["statistiques", "users"])]
    private ?string $telephone = null;

    #[ORM\Column(length: 255)]
    #[Groups(["statistiques", "caisses",   "users", "select_utilisateurs", "operationCaisses" ,"operabilite"])]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Groups(["statistiques","users"])]
    private ?string $fonction = null;

    #[ORM\Column]
    #[Groups(["statistiques"])]
    private ?DateTime $createdAt = null;

    #[ORM\Column]
    #[Groups(["statistiques"])]
    private ?DateTime $updatedAt = null;

    #[ORM\OneToMany(targetEntity: Caisse::class, mappedBy: 'utilisateursId')]
    #[Groups(["statistiques"])]
    private Collection $caisses;

    // #[ORM\OneToOne(inversedBy: 'user', cascade: ['persist', 'remove'])]
    // #[Groups(["users", "select_user"])]
    // private ?Partenaires $partenairesId = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[Groups(["users", "select_user"])]
    private ?Partenaires $partenairesId = null;

    public function __construct()
    {
        $this->caisses = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }


    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->telephone;
    }

    /**
     * @see UserInterface
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        return $roles;
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    

    public function getIdSite(): ?Site
    {
        return $this->id_site;
    }

    public function setIdSite(?Site $id_site): static
    {
        $this->id_site = $id_site;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getFonction(): ?string
    {
        return $this->fonction;
    }

    public function setFonction(string $fonction): static
    {
        $this->fonction = $fonction;

        return $this;
    }

    #[ORM\PrePersist]

    public function setCreatedAtValue(): void
    {
        $this->createdAt = new DateTime();
    }

    public function getCreatedAt(): ?DateTime
    {
        // dd($this->createdAt);
        return $this->createdAt;
    }
    


    
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new DateTime();
    }

    public function getUpdatedAtValue(): ?DateTime
    {
        return $this->updatedAt;
    }

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
            $caiss->setUtilisateursId($this);
        }

        return $this;
    }

    public function removeCaiss(Caisse $caiss): static
    {
        if ($this->caisses->removeElement($caiss)) {
            // set the owning side to null (unless already changed)
            if ($caiss->getUtilisateursId() === $this) {
                $caiss->setUtilisateursId(null);
            }
        }

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

    // public function getIdPartenaire(): ?Partenaires
    // {
    //     return $this->partenairesId;
    // }

    // public function setIdPartenaire(?Partenaires $partenairesId): static
    // {
        
    //     $this->partenairesId = $partenairesId;

    //     return $this;
    // }


}
