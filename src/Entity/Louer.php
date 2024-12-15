<?php

namespace App\Entity;

use App\Repository\LouerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LouerRepository::class)]
class Louer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, Local>
     */
    #[ORM\OneToMany(targetEntity: Local::class, mappedBy: 'louer', orphanRemoval: true)]
    private Collection $local;

    public function __construct()
    {
        $this->local = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Local>
     */
    public function getLocal(): Collection
    {
        return $this->local;
    }

    public function addLocal(Local $local): static
    {
        if (!$this->local->contains($local)) {
            $this->local->add($local);
            $local->setLouer($this);
        }

        return $this;
    }

    public function removeLocal(Local $local): static
    {
        if ($this->local->removeElement($local)) {
            // set the owning side to null (unless already changed)
            if ($local->getLouer() === $this) {
                $local->setLouer(null);
            }
        }

        return $this;
    }
}
