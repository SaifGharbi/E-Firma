<?php

namespace App\Entity;

use App\Repository\ParcelleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParcelleRepository::class)]
class Parcelle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Validation for 'Nom' field
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de la parcelle ne doit pas être vide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le nom de la parcelle ne peut pas dépasser 50 caractères."
    )]
    #[Assert\Type(type: 'string', message: "Le nom doit être une chaîne de caractères.")]
    private ?string $nom = null;

    // Validation for 'Superficie' field
    #[ORM\Column]
    #[Assert\NotBlank(message: "La superficie ne doit pas être vide.")]
    #[Assert\Positive(message: "La superficie doit être positive.")]
    private ?int $superficie = null;

    // Validation for 'Localisation' field
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La localisation ne doit pas être vide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "La localisation ne peut pas dépasser 50 caractères."
    )]
    #[Assert\Type(type: 'string', message: "La localisation doit être une chaîne de caractères.")]
    private ?string $localisation = null;

    /**
     * @var Collection<int, CultureParcelle>
     */
    #[ORM\OneToMany(targetEntity: CultureParcelle::class, mappedBy: 'parcelle')]
    private Collection $cultureParcelles;

    public function __construct()
    {
        $this->cultureParcelles = new ArrayCollection();
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

    public function getSuperficie(): ?int
    {
        return $this->superficie;
    }

    public function setSuperficie(int $superficie): static
    {
        $this->superficie = $superficie;

        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): static
    {
        $this->localisation = $localisation;

        return $this;
    }

    /**
     * @return Collection<int, CultureParcelle>
     */
    public function getCultureParcelles(): Collection
    {
        return $this->cultureParcelles;
    }

    public function addCultureParcelle(CultureParcelle $cultureParcelle): static
    {
        if (!$this->cultureParcelles->contains($cultureParcelle)) {
            $this->cultureParcelles->add($cultureParcelle);
            $cultureParcelle->setParcelle($this);
        }

        return $this;
    }

    public function removeCultureParcelle(CultureParcelle $cultureParcelle): static
    {
        if ($this->cultureParcelles->removeElement($cultureParcelle)) {
            // set the owning side to null (unless already changed)
            if ($cultureParcelle->getParcelle() === $this) {
                $cultureParcelle->setParcelle(null);
            }
        }

        return $this;
    }
}
