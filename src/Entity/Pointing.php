<?php

namespace App\Entity;

use App\Repository\PointingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PointingRepository::class)
 */
class Pointing
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Heure du pointage d'entré
     * @ORM\Column(type="datetime")
     */
    private $timeIn;

    /**
     * Heur du pointage de sortie
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $timeOut;

    /**
     * Type de pointage (In, Out)
     * 
     */
    private $type; //@ORM\Column(type="string")

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="pointings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $employee;

    /**
     * Statut du pointage (on pending, approved, disapproved )
     * @ORM\Column(type="string", length=20)
     */
    private $statut;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTimeIn(): ?\DateTimeInterface
    {
        return $this->timeIn;
    }

    public function setTimeIn(\DateTimeInterface $timeIn): self
    {
        $this->timeIn = $timeIn;

        return $this;
    }

    public function getTimeOut(): ?\DateTimeInterface
    {
        return $this->timeOut;
    }

    public function setTimeOut(\DateTimeInterface $timeOut): self
    {
        $this->timeOut = $timeOut;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getEmployee(): ?User
    {
        return $this->employee;
    }

    public function setEmployee(?User $employee): self
    {
        $this->employee = $employee;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;

        return $this;
    }
}
