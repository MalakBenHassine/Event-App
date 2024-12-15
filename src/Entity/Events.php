<?php
namespace App\Entity;

use App\Repository\EventsRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: EventsRepository::class)]
class Events
{
#[ORM\Id]
#[ORM\GeneratedValue]
#[ORM\Column]
private ?int $id = null;

#[ORM\Column(length: 255)]
private ?string $nom = null;

#[ORM\Column(type: Types::DATE_MUTABLE)]
private ?\DateTimeInterface $date = null;

#[ORM\Column(length: 255)]
private ?string $description = null;

#[ORM\ManyToOne(targetEntity: Local::class, inversedBy: "events")]
#[ORM\JoinColumn(nullable: false)]
private ?Local $local = null;

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

public function getDate(): ?\DateTimeInterface
{
return $this->date;
}

public function setDate(\DateTimeInterface $date): static
{
    $this->date = $date;

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

public function getLocal(): ?Local
{
return $this->local;
}
    public function setLocal(?Local $local): static
    {
        $this->local = $local;

        // Ensure the relationship is consistent
        if ($local !== null && !$local->getEvents()->contains($this)) {
            $local->addEvent($this);
        }

        return $this;
    }



}
