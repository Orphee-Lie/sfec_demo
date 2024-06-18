<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\OperationCaisseRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: OperationCaisseRepository::class)]
class OperationCaisse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["stat","operationCaisses","select","caisses"])]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["stat","operationCaisses","select","caisses","operabilite"])]
    private ?int $cout = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(["stat","operationCaisses","operabilite"])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
 
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'operationCaisses')]
    #[Groups(["operationCaisses", "stat","journalCaisse","operabilite"])]
    private ?Caisse $caisseId = null;

    #[ORM\ManyToOne(inversedBy: 'operationCaisses')]
    #[Groups(["operationCaisses","stat",])]
    private ?Partenaires $partenairesId = null;

    #[Groups(["operationCaisses","stat",])]
    public ?array $cout_prestation_par_partenaire = [];

    #[ORM\Column(nullable: true)]
    // #[Groups(["stat","operationCaisses","select","caisses","operabilite"])]
    private ?string $interoperability_data = null;

    public function __construct($cout_prestation_par_partenaire = [])
    { 
        try {
            //code...
            $data = $this->getCaisseId()->getPrestations()->getCoutPrestations()->toArray();
            $cout_prestation_par_partenaire = $data;
            $this->cout_prestation_par_partenaire = $this->getCoutPrestationParPartenaire();
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setinteroperabilityData(?string $interoperability_data): static
    {
        $this->interoperability_data = $interoperability_data;
        return $this;
    }

    public function getinteroperabilityData(): ?string
    {
        return $this->interoperability_data;
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

    public function setCreatedAt(?\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCaisseId(): ?Caisse
    {
        return $this->caisseId;
    }

    public function setCaisseId(?Caisse $caisseId): static
    {
        $this->caisseId = $caisseId;

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

    public function getCoutPrestationParPartenaire() 
    {
        $tab = [];
        foreach ($this->getCaisseId()->getprestations()->getCoutPrestations()->toArray() as $key => $value) {
            # code...
            $tab[] = [
                "cout" => $value->getCout(),
                "id_partenaire" => $value->getPartenairesId()->getId(),
            ];
        }
        $this->cout_prestation_par_partenaire = $tab;
        return $this->cout_prestation_par_partenaire;
    }

    public function setCoutPrestationParPartenaire($cout_prestation_par_partenaire): array
    {
        $tab = [];
        foreach ($this->getCaisseId()->getprestations()->getCoutPrestations()->toArray() as $key => $value) {
            # code...
            $tab[] = [
                "cout" => $value->getCout(),
                "id_partenaire" => $value->getPartenairesId()->getId(),
            ];
        }
        $this->cout_prestation_par_partenaire = $tab;
        return $this->cout_prestation_par_partenaire;
    }

}
