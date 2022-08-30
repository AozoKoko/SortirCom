<?php

namespace App\Entity;

use App\Repository\MotifRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MotifRepository::class)
 */
class Motif
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=1500, nullable=true)
     */
    private $libelle;

    /**
     * @ORM\OneToOne(targetEntity=Sortie::class, inversedBy="motif", cascade={"persist", "remove"})
     */
    private $annulation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(?string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getAnnulation(): ?Sortie
    {
        return $this->annulation;
    }

    public function setAnnulation(?Sortie $annulation): self
    {
        $this->annulation = $annulation;

        return $this;
    }
}
