<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\SiteRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SiteRepository::class)]
class Site
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["statistiques","users","sites", "select", "select_site", "operationCaisses","caisses"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["statistiques","users","sites", "select", "select_site", "operationCaisses","operabilite"])]
    private ?string $nom = null;


    #[ORM\Column]
    #[Groups(["statistiques","sites"])]
    private ?DateTime $createdAt = null;

    #[ORM\Column]
    #[Groups(["statistiques"])]
    private ?DateTime $updatedAt = null;


    #[ORM\OneToONe(targetEntity: User::class, mappedBy: 'id_site')]


    #[Groups(["statistiques"])]
    private Collection $users;
    public function __construct()
    {
        $this->createdAt = new DateTime();
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

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, user>
     */
    public function getusers(): Collection
    {
        return $this->users;
    }

    public function adduser(user $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setIdSite($this);
        }

        return $this;
    }

    public function removeuser(user $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getIdSite() === $this) {
                $user->setIdSite($this);
            }
        }

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
}
