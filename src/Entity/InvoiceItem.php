<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\InvoiceItemRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

/**
 * @ORM\Entity(repositoryClass=InvoiceItemRepository::class)
 */
class InvoiceItem
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     * @Assert\Positive
     */
    private $pu;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Positive
     */
    private $quantity;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez la désignation de l'élément")
     */
    private $designation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     */
    private $reference;

    /**
     * @ORM\ManyToMany(targetEntity=Invoice::class, inversedBy="invoiceItems")
     */
    private $invoice;

    private $amountBrutHT;

    private $amountNetHT;

    private $available;

    private $isChanged;

    /**
     * @ORM\Column(type="float")
     * @Assert\PositiveOrZero
     */
    private $remise;

    public function __construct()
    {
        $this->invoice = new ArrayCollection();
    }

    public function getAmountBrutHT(): ?float
    {
        return $this->amountBrutHT;
    }

    public function setAmountBrutHT(float $amountBrutHT): self
    {
        $this->amountBrutHT = $amountBrutHT;

        return $this;
    }

    public function getAmountNetHT(): ?float
    {
        return $this->amountNetHT;
    }

    public function setAmountNetHT(float $amountNetHT): self
    {
        $this->amountNetHT = $amountNetHT;

        return $this;
    }

    public function getAvailable(): ?int
    {
        return $this->available;
    }

    public function setAvailable(int $available): self
    {
        $this->available = $available;

        return $this;
    }

    public function getIsChanged(): ?int
    {
        return $this->isChanged;
    }

    public function setIsChanged(int $isChanged): self
    {
        $this->isChanged = $isChanged;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPu(): ?float
    {
        return $this->pu;
    }

    public function setPu(float $pu): self
    {
        $this->pu = $pu;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * @return Collection|Invoice[]
     */
    public function getInvoice(): Collection
    {
        return $this->invoice;
    }

    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->invoice->contains($invoice)) {
            $this->invoice[] = $invoice;
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoice->contains($invoice)) {
            $this->invoice->removeElement($invoice);
        }

        return $this;
    }

    public function getRemise(): ?float
    {
        return $this->remise;
    }

    public function setRemise(float $remise): self
    {
        $this->remise = $remise;

        return $this;
    }
}
