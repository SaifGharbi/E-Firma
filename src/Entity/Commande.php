<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date;

    #[ORM\Column]
    private ?float $total = null;

    #[ORM\Column(length: 255, options: ["default" => "Not Treated"])]
    #[Assert\Choice(choices: ['Not Treated', 'Non Confirmé', 'Confirmé', 'Treated'], message: 'Please choose a valid status (Not Treated, Non Confirmé, Confirmé, or Treated).')]
    private string $statut = "Not Treated";


    #[ORM\OneToMany(targetEntity: Produit::class, mappedBy: 'commande', cascade: ['persist', 'remove'])]
    private Collection $produits;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'commandes')]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    public function __construct()
    {
        $this->date = new \DateTime();
        $this->produits = new ArrayCollection();
        $this->statut = "Not Treated"; // Ensure statut is never null
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): static
    {
        $this->total = $total;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produit $produit): self
    {
        if (!$this->produits->contains($produit)) {
            $this->produits[] = $produit;
            $produit->setCommande($this);
        }
        return $this;
    }

    public function removeProduit(Produit $produit): self
    {
        if ($this->produits->removeElement($produit)) {
            // Set the owning side to null (unless already changed)
            if ($produit->getCommande() === $this) {
                $produit->setCommande(null);
            }
        }
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    // Helper method to calculate total based on produits
    public function calculateTotal(): float
    {
        return $this->produits->reduce(function ($total, Produit $produit) {
            return $total + ($produit->getPrix() * $produit->getQuantite());
        }, 0.0);
    }
    public function getLivraison(): ?Livraison
    {
        return $this->livraison;
    }

    public function setLivraison(?Livraison $livraison): static
    {
        $this->livraison = $livraison;
        // Set the owning side of the relationship if necessary
        if ($livraison && $livraison->getCommande() !== $this) {
            $livraison->setCommande($this);
        }

        return $this;
    }
}