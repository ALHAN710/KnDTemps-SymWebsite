<?php

namespace App\Entity;

use DateTime;
use DateInterval;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=InvoiceRepository::class)
 * 
 * @ORM\HasLifecycleCallbacks()
 * 
 */
class Invoice
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $paymentStatus;

    /**
     * @ORM\Column(type="boolean")
     */
    private $completedStatus;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deliveryStatus;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="invoices")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="float")
     * @Assert\PositiveOrZero
     */
    private $fixReduction;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $completedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deliverAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $payAt;


    private $periodofvalidity;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     */
    private $paymentMode;


    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\PositiveOrZero
     */
    private $duration;

    /**
     * @ORM\ManyToMany(targetEntity=InvoiceItem::class, mappedBy="invoice")
     * @Assert\Valid()
     */
    private $invoiceItems;

    /**
     * @ORM\Column(type="float")
     */
    private $advancePayment;

    public function __construct()
    {
        $this->invoiceItems = new ArrayCollection();
    }

    private $totalAmountNetHT = 0.0;

    private $itemsRemise = 0.0;

    /**
     * @ORM\ManyToOne(targetEntity=Enterprise::class, inversedBy="invoices")
     */
    private $enterprise;

    public function getTotalAmountBrutHT(): float
    {
        $this->totalAmountNetHT = 0.0;
        $this->itemsRemise = 0.0;
        $totalAmountBrutHT = 0.0;

        $items = $this->getInvoiceItems();
        foreach ($items as $item) {
            $tmp = ($item->getQuantity() * $item->getPu());
            $totalAmountBrutHT += $tmp;
            $remise = (($tmp * $item->getRemise()) / 100.0);
            $this->itemsRemise += $remise;
            $this->totalAmountNetHT += $tmp - $remise;
        }
        //$totalAmountBrutHT = number_format((float) $totalAmountBrutHT, 2, '.', ' ');
        return $totalAmountBrutHT;
    }

    public function getItemAmountNetHT()
    {
        $this->getTotalAmountBrutHT();
        return $this->itemAmountNetHT;
    }
    public function getItemsRemise()
    {
        $this->getTotalAmountBrutHT();
        return $this->itemsRemise;
    }

    public function getTotalAmountNetHT(): float
    {
        $this->getTotalAmountBrutHT();
        $amountNetHT = $this->totalAmountNetHT - $this->getFixReduction();
        /*$items = $this->getItemAmountNetHT();
        dump($items);
        foreach ($items as $key => $value) {
            $amountNetHT += $value;
            dump($value);
        }*/
        //$amountNetHT = number_format((float) $amountNetHT, 2, '.', ' ');
        return $amountNetHT;
    }

    public function getTaxes(): float
    {
        $totalAmountBrutHT = $this->getTotalAmountNetHT();
        //$taxes = ($this->getUser()->getEnterprise()->getTva() * $totalAmountBrutHT) / 100.0;
        //$taxes = number_format((float) $taxes, 2, '.', ' ');
        return 0; //$taxes;
    }

    public function getAmountTTC(): float
    {

        $itemsAmountSubTotal = $this->getTotalAmountNetHT();
        $taxes = $this->getTaxes();
        $totalTTC = $itemsAmountSubTotal + $taxes;
        //$totalTTC = number_format((float) $totalTTC, 2, '.', ' ');
        return $totalTTC;
    }

    public function getAmountReduction(): float
    {
        /*$items = $this->getItemsRemise();
        $itemsRemise = $this->getItemsRemise();
        foreach ($items as $key => $value) {
            $itemsRemise += $value;
        }*/

        $totalAmountReduction = $this->getItemsRemise() + $this->getFixReduction();
        //$totalAmountReduction = number_format((float) $totalAmountReduction, 2, '.', ' ');
        return $totalAmountReduction;
    }

    public function getAmountNetToPaid(): float
    {
        //totalAmount = itemsAmountSubTotal + deliveryFees + taxes - totalPromoAmount;
        // $totalTTC = $this->getAmountTTC();
        // $totalAmountReduction = $this->getAmountReduction();
        // $totalAmountNetToPaid = $totalTTC - $totalAmountReduction;
        //$totalAmountNetToPaid = number_format((float) $totalAmountNetToPaid, 2, '.', ' ');

        // return $totalAmountNetToPaid;
        return $this->getAmountTTC();
    }

    public function getAmountRestToPaid(): float
    {
        //$totalNetToPaid = $this->getAmountNetToPaid();
        $totalAmountRestToPaid = $this->getAmountNetToPaid() - $this->getAdvancePayment();

        return $totalAmountRestToPaid;
    }

    /**
     * Permet d'initialiser le status de livraison de la commande à false
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * 
     * @return void
     */
    /*public function initializeAdvancePayment()
    {
        if (($this->getCompletedStatus() == true) || ($this->getPaymentStatus() == true)) {
            $this->setAdvancePayment($this->getAmountNetToPaid());
        }
    }*/


    /**
     * Permet d'initialiser le status de livraison de la commande à false
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * 
     * @return void
     */
    public function initializeDeliveryStatus()
    {
        if (empty($this->deliveryStatus)) {
            $this->deliveryStatus = false;
        }
    }

    /**
     * Permet d'initialiser le status completed de la commande à false
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * 
     * @return void
     */
    public function initializeCompletedStatus()
    {
        if (empty($this->completedStatus)) {
            $this->completedStatus = false;
        }
    }

    /**
     * Permet d'initialiser le status de paiement de la commande à false
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * 
     * @return void
     */
    public function initializePaymentStatus()
    {
        if (empty($this->paymentStatus)) {
            $this->paymentStatus = false;
        }
    }

    /**
     * Permet d'initialiser la date de création de l'utilisateur
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * 
     * @return void
     */
    public function initializeCreatedAt()
    {
        if (empty($this->createdAt)) {
            $this->createdAt = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('Africa/Douala'));
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        //date('Y') . date('m') . date('d');
        return $this->getCreatedAt()->format('Y') . $this->getCreatedAt()->format('m') . $this->getCreatedAt()->format('d') . $this->getId() . '';
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getPaymentStatus(): ?bool
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(bool $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    public function getCompletedStatus(): ?bool
    {
        return $this->completedStatus;
    }

    public function setCompletedStatus(bool $completedStatus): self
    {
        $this->completedStatus = $completedStatus;

        return $this;
    }

    public function getDeliveryStatus(): ?bool
    {
        return $this->deliveryStatus;
    }

    public function setDeliveryStatus(bool $deliveryStatus): self
    {
        $this->deliveryStatus = $deliveryStatus;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getFixReduction(): ?float
    {
        return $this->fixReduction;
    }

    public function setFixReduction(float $fixReduction): self
    {
        $this->fixReduction = $fixReduction;

        return $this;
    }

    public function getCompletedAt(): ?\DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeInterface $completedAt): self
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    public function getDeliverAt(): ?\DateTimeInterface
    {
        return $this->deliverAt;
    }

    public function setDeliverAt(?\DateTimeInterface $deliverAt): self
    {
        $this->deliverAt = $deliverAt;

        return $this;
    }

    public function getPayAt(): ?\DateTimeInterface
    {
        return $this->payAt;
    }

    public function setPayAt(?\DateTimeInterface $payAt): self
    {
        $this->payAt = $payAt;

        return $this;
    }


    public function getPeriodofvalidity(): ?\DateTimeInterface
    {
        if (!empty($this->duration)) {
            $this->periodofvalidity = new DateTime($this->createdAt->format('Y/m/d'));
            $this->periodofvalidity->add(new DateInterval('P' . $this->duration . 'D'));
            if ($this->periodofvalidity) return $this->periodofvalidity;
        }
        return null;
    }

    /**
     * Fonction de détermination de la validité d'un devis ou d'un abonnement 
     * Renvoi false si valide ou en
     */
    public function getAlert()
    {

        $nowDate = new DateTime("now");
        $this->periodofvalidity = new DateTime($this->createdAt->format('Y/m/d'));
        $this->periodofvalidity->add(new DateInterval('P' . $this->duration . 'D'));
        $interval = $nowDate->diff($this->periodofvalidity);
        //$interval = $this->periodofvalidity->diff($nowDate);
        if ($interval) {
            //return gettype($interval->format('d'));
            //return $interval->format('%R%a days');// '+29 days'
            //return $interval->days; //Nombre de jour total de différence entre les dates 
            return !$interval->invert; // 
        }
        return null;
    }

    public function getPaymentMode(): ?string
    {
        return $this->paymentMode;
    }

    public function setPaymentMode(string $paymentMode): self
    {
        $this->paymentMode = $paymentMode;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * @return Collection|InvoiceItem[]
     */
    public function getInvoiceItems(): Collection
    {
        return $this->invoiceItems;
    }

    public function addInvoiceItem(InvoiceItem $invoiceItem): self
    {
        if (!$this->invoiceItems->contains($invoiceItem)) {
            $this->invoiceItems[] = $invoiceItem;
            $invoiceItem->addInvoice($this);
        }

        return $this;
    }

    public function removeInvoiceItem(InvoiceItem $invoiceItem): self
    {
        if ($this->invoiceItems->contains($invoiceItem)) {
            $this->invoiceItems->removeElement($invoiceItem);
            $invoiceItem->removeInvoice($this);
        }

        return $this;
    }

    public function getAdvancePayment(): ?float
    {
        return $this->advancePayment;
    }

    public function setAdvancePayment(float $advancePayment): self
    {
        $this->advancePayment = $advancePayment;

        return $this;
    }

    public function getEnterprise(): ?Enterprise
    {
        return $this->enterprise;
    }

    public function setEnterprise(?Enterprise $enterprise): self
    {
        $this->enterprise = $enterprise;

        return $this;
    }
}
