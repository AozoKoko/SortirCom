<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Integer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SortieRepository", repositoryClass=SortieRepository::class)
 */
class Sortie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Vous devez saisir un nom")
     * @Assert\Length(min="2", max="20",minMessage="Trop court au moins 2 caractères", maxMessage="Trop long ! max 20 caractères")
     * @ORM\Column(type="string", length=100)
     */
    private $nom;

    /**
     * @Assert\NotBlank(message="Vous devez saisir une date")
     * @Assert\GreaterThan("now", message="La date saisie est invalide")
     * @Assert\Expression("value > this.getDateLimiteInscription()", message="La date de début doit être supérieur à la date d'inscription")
     * @ORM\Column(type="datetime")
     */
    private $dateHeureDebut;

    /**
     * @Assert\NotBlank(message="Vous devez saisir une sortie")
     * @Assert\Length(min="2", max="1000",minMessage="Trop petit au moins une durée de 2 minutes", maxMessage="Trop grand ! max 1000 minutes")
     * @Assert\GreaterThan("0", message="La durée saisie est invalide")
     * @ORM\Column(type="integer")
     */
    private $duree;

    /**
     * @Assert\NotBlank(message="Vous devez saisir une date limite d'inscription")
     * @Assert\GreaterThan("now", message="La date saisie est invalide")
     * @ORM\Column(type="datetime")
     */
    private $dateLimiteInscription;

    /**
     * @Assert\Length(min="2", max="20",minMessage="Trop petit au moins 2 participants", maxMessage="Trop grand ! max 20 participants")
     * @Assert\NotBlank(message="Vous devez saisir un nombre d'inscription max")
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbInscriptionsMax;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $infosSortie;

    /**
     * @ORM\ManyToMany(targetEntity=Participant::class, mappedBy="inscrits")
     */
    private $participants;

    /**
     * @ORM\ManyToOne(targetEntity=Participant::class, inversedBy="organisateurs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organisateur;

    /**
     * @ORM\ManyToOne(targetEntity=Campus::class, inversedBy="siteOrganisateur")
     * @ORM\JoinColumn(nullable=false)
     */
    private $campus;

    /**
     * @ORM\ManyToOne(targetEntity=Etat::class, inversedBy="sorties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $etats;

    /**
     * @ORM\ManyToOne(targetEntity=Lieu::class, inversedBy="sorties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $lieu;

    /**
     * @ORM\OneToOne(targetEntity=Motif::class, mappedBy="annulation", cascade={"persist", "remove"})
     */
    private $motif;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateHeureDebut(): ?\DateTimeInterface
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTimeInterface $dateHeureDebut): self
    {
        $this->dateHeureDebut = $dateHeureDebut;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateLimiteInscription(): ?\DateTimeInterface
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(\DateTimeInterface $dateLimiteInscription): self
    {
        $this->dateLimiteInscription = $dateLimiteInscription;

        return $this;
    }

    public function getNbInscriptionsMax(): ?int
    {
        return $this->nbInscriptionsMax;
    }

    public function setNbInscriptionsMax(?int $nbInscriptionsMax): self
    {
        $this->nbInscriptionsMax = $nbInscriptionsMax;

        return $this;
    }

    public function getInfosSortie(): ?string
    {
        return $this->infosSortie;
    }

    public function setInfosSortie(?string $infosSortie): self
    {
        $this->infosSortie = $infosSortie;

        return $this;
    }

    /**
     * @return Collection<int, Participant>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
            $participant->addInscrit($this);
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): self
    {
        if ($this->participants->removeElement($participant)) {
            $participant->removeInscrit($this);
        }

        return $this;
    }

    public function getOrganisateur(): ?Participant
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?Participant $organisateur): self
    {
        $this->organisateur = $organisateur;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): self
    {
        $this->campus = $campus;

        return $this;
    }

    public function getEtats(): ?Etat
    {
        return $this->etats;
    }

    public function setEtats(?Etat $etats): self
    {
        $this->etats = $etats;

        return $this;
    }

    public function getLieu(): ?Lieu
    {
        return $this->lieu;
    }

    public function setLieu(?Lieu $lieu): self
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getMotif(): ?Motif
    {
        return $this->motif;
    }

    public function setMotif(?Motif $motif): self
    {
        // unset the owning side of the relation if necessary
        if ($motif === null && $this->motif !== null) {
            $this->motif->setAnnulation(null);
        }

        // set the owning side of the relation if necessary
        if ($motif !== null && $motif->getAnnulation() !== $this) {
            $motif->setAnnulation($this);
        }

        $this->motif = $motif;

        return $this;
    }

}
