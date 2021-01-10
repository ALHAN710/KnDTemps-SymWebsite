<?php

namespace App\Entity;

use App\Repository\SubscriptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SubscriptionRepository::class)
 */
class Subscription
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $employeeNumber;

    /**
     * @ORM\OneToMany(targetEntity=Enterprise::class, mappedBy="subscription")
     */
    private $enterprises;

    /**
     * @ORM\Column(type="json")
     */
    private $tarifs = [];

    private $subscriptionPrice;

    private $subscriptionMaxEmployee;

    private $subscriptionName;

    private $subscriptionDuration;

    public function __construct()
    {
        $this->enterprises = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmployeeNumber(): ?int
    {
        return $this->employeeNumber;
    }

    public function setEmployeeNumber(int $employeeNumber): self
    {
        $this->employeeNumber = $employeeNumber;

        return $this;
    }

    /**
     * @return Collection|Enterprise[]
     */
    public function getEnterprises(): Collection
    {
        return $this->enterprises;
    }

    public function addEnterprise(Enterprise $enterprise): self
    {
        if (!$this->enterprises->contains($enterprise)) {
            $this->enterprises[] = $enterprise;
            $enterprise->setSubscription($this);
        }

        return $this;
    }

    public function removeEnterprise(Enterprise $enterprise): self
    {
        if ($this->enterprises->contains($enterprise)) {
            $this->enterprises->removeElement($enterprise);
            // set the owning side to null (unless already changed)
            if ($enterprise->getSubscription() === $this) {
                $enterprise->setSubscription(null);
            }
        }

        return $this;
    }

    public function getTarifs(): ?array
    {
        return $this->tarifs;
    }

    public function setTarifs(array $tarifs): self
    {
        $this->tarifs = $tarifs;

        return $this;
    }

    public function getSubscriptionDuration()
    {
        return $this->subscriptionDuration;
    }

    public function setSubscriptionDuration($subscriptionDuration): self
    {
        $this->subscriptionDuration = $subscriptionDuration;

        return $this;
    }

    /**
     * Get the value of subscriptionPrice
     */
    public function getSubscriptionPrice()
    {
        return $this->subscriptionPrice;
    }

    /**
     * Set the value of subscriptionPrice
     *
     * @return  self
     */
    public function setSubscriptionPrice($subscriptionPrice)
    {
        $this->subscriptionPrice = $subscriptionPrice;

        return $this;
    }

    /**
     * Get the value of subscriptionMaxEmployee
     */
    public function getSubscriptionMaxEmployee()
    {
        return $this->subscriptionMaxEmployee;
    }

    /**
     * Set the value of subscriptionMaxEmployee
     *
     * @return  self
     */
    public function setSubscriptionMaxEmployee($subscriptionMaxEmployee)
    {
        $this->subscriptionMaxEmployee = $subscriptionMaxEmployee;

        return $this;
    }

    /**
     * Get the value of subscriptionName
     */
    public function getSubscriptionName()
    {
        return $this->subscriptionName;
    }

    /**
     * Set the value of subscriptionName
     *
     * @return  self
     */
    public function setSubscriptionName($subscriptionName)
    {
        $this->subscriptionName = $subscriptionName;

        return $this;
    }
}
