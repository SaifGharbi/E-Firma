<?php

namespace App\Entity;

use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'The service name cannot be blank.')]
    #[Assert\Length(max: 255, maxMessage: 'The service name cannot exceed {{ limit }} characters.')]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'The description cannot be blank.')]
    #[Assert\Length(max: 255, maxMessage: 'The description cannot exceed {{ limit }} characters.')]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'The price cannot be blank.')]
    #[Assert\Type(type: 'float', message: 'The price must be a number.')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'The price must be greater than or equal to 0.')]
    private ?float $prix = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'The type cannot be blank.')]
    #[Assert\Length(max: 255, maxMessage: 'The type cannot exceed {{ limit }} characters.')]
    #[Assert\Choice(choices: ['Consultation', 'Maintenance', 'Delivery', 'Other'], message: 'The type must be one of: Consultation, Maintenance, Delivery, or Other.')]
    private ?string $type = null;

    /**
     * @var Collection<int, RendezVous>
     */
    #[ORM\OneToMany(targetEntity: RendezVous::class, mappedBy: 'service', cascade: ['persist', 'remove'])]
    private Collection $rendezVous; // ✅ Renamed from 'rendezVouses' to 'rendezVous'

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'services')] // Many services belong to one user
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id', nullable: true)] // Foreign key to User.id
    private ?User $user = null; // Reference to the User entity

    public function __construct()
    {
        $this->rendezVous = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, RendezVous>
     */
    public function getRendezVous(): Collection
    {
        return $this->rendezVous;
    }

    public function addRendezVous(RendezVous $rendezVous): static
    {
        if (!$this->rendezVous->contains($rendezVous)) {
            $this->rendezVous->add($rendezVous);
            $rendezVous->setService($this);
        }

        return $this;
    }

    public function removeRendezVous(RendezVous $rendezVous): static
    {
        if ($this->rendezVous->removeElement($rendezVous)) {
            // set the owning side to null (unless already changed)
            if ($rendezVous->getService() === $this) {
                $rendezVous->setService(null);
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
}