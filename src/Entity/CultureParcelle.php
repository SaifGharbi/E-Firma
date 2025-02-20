<?php

namespace App\Entity;

use App\Repository\CultureParcelleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CultureParcelleRepository::class)]
class CultureParcelle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le type de culture ne doit pas être vide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le type de culture ne peut pas dépasser 255 caractères."
    )]
    private ?string $type_culture = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date de plantation ne doit pas être vide.")]
    #[Assert\GreaterThan(
        value: "today",
        message: "La date de plantation doit être dans le futur."
    )]
    private ?\DateTimeInterface $date_plantation = null;

    // Validation for 'date_recolte_prevue' field
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date de récolte prévue ne doit pas être vide.")]
    #[Assert\GreaterThan(
        propertyPath: "date_plantation",
        message: "La date de récolte prévue doit être après la date de plantation."
    )]
    private ?\DateTimeInterface $date_recolte = null;

     // Validation for 'rendement_estime' field
     #[ORM\Column]
     #[Assert\NotBlank(message: "Le rendement estimé ne doit pas être vide.")]
     #[Assert\Positive(message: "Le rendement estimé doit être positif.")]
    private ?int $rendement_estime = null;

    #[ORM\ManyToOne(inversedBy: 'cultureParcelles')]
    private ?Parcelle $parcelle = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeCulture(): ?string
    {
        return $this->type_culture;
    }

    public function setTypeCulture(string $type_culture): static
    {
        $this->type_culture = $type_culture;

        return $this;
    }

    public function getDatePlantation(): ?\DateTimeInterface
    {
        return $this->date_plantation;
    }

    public function setDatePlantation(\DateTimeInterface $date_plantation): static
    {
        $this->date_plantation = $date_plantation;

        return $this;
    }

    public function getDateRecolte(): ?\DateTimeInterface
    {
        return $this->date_recolte;
    }

    public function setDateRecolte(\DateTimeInterface $date_recolte): static
    {
        $this->date_recolte = $date_recolte;

        return $this;
    }

    public function getRendementEstime(): ?int
    {
        return $this->rendement_estime;
    }

    public function setRendementEstime(int $rendement_estime): static
    {
        $this->rendement_estime = $rendement_estime;

        return $this;
    }

    public function getParcelle(): ?Parcelle
    {
        return $this->parcelle;
    }

    public function setParcelle(?Parcelle $parcelle): static
    {
        $this->parcelle = $parcelle;

        return $this;
    }
}
