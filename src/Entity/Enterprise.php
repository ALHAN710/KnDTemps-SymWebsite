<?php

namespace App\Entity;

use DateTime;
use DateInterval;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\EnterpriseRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=EnterpriseRepository::class)
 * 
 * @ORM\HasLifecycleCallbacks()
 * 
 * @UniqueEntity(
 *  fields={"socialReason", "phoneNumber"},
 *  message="Une entreprise est déjà enregistrée avec ses paramètres(raison sociale et tél), veuillez les modifier svp !"
 * )
 * 
 */
class Enterprise
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez renseigner la raison sociale ou le nom")
     * @Assert\Length(max=255, minMessage="255 caractères Max !")
     */
    private $socialReason;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $niu;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rccm;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez renseigner le numéro de téléphone")
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="enterprise", orphanRemoval=true)
     */
    private $users;


    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $logo;


    /**
     * @ORM\ManyToOne(targetEntity=Subscription::class, inversedBy="enterprises")
     * 
     */
    private $subscription;

    /**
     * @ORM\Column(type="integer")
     */
    private $subscriptionDuration;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $subscribeAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $town;

    private $subscriptionPrice;

    private $subscriptionMaxEmployee;

    private $tarifs;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActivated;

    /**
     * @ORM\OneToMany(targetEntity=Office::class, mappedBy="enterprise", orphanRemoval=true)
     */
    private $offices;

    /**
     * @ORM\OneToMany(targetEntity=Team::class, mappedBy="enterprise", orphanRemoval=true)
     */
    private $teams;

    /**
     * @ORM\OneToMany(targetEntity=PointingLocation::class, mappedBy="enterprise", orphanRemoval=true)
     */
    private $pointingLocations;

    /**
     * @ORM\Column(type="float")
     */
    private $timeZone;

    /**
     * @ORM\OneToMany(targetEntity=Invoice::class, mappedBy="enterprise")
     */
    private $invoices;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="enterprises")
     */
    private $registerBy;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->offices = new ArrayCollection();
        $this->teams = new ArrayCollection();
        $this->pointingLocations = new ArrayCollection();
        $this->invoices = new ArrayCollection();
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

    /**
     * Permet d'initialiser l'activation de l'abonnement du client Entreprise
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * 
     * @return void
     */
    public function initializeIsActivated()
    {
        if (empty($this->isActivated)) {
            $this->isActivated = false;
        }
    }

    /**
     * Permet d'obtenir le nombre d'employé d'une entreprise
     *
     * @return void
     */
    public function getNumberOfEmployees()
    {
        return count($this->getUsers());
    }

    /**
     * Permet de déterminer la date de fin d'abonnement
     */
    public function endSubscription()
    {
        if ($this->subscribeAt) {
            $periodofvalidity = new DateTime($this->subscribeAt->format('Y/m/d'));
            $periodofvalidity->add(new DateInterval('P' . $this->subscriptionDuration . 'M'));

            return $periodofvalidity;
        } else {
            return $this->createdAt;
        }
    }

    public function subscriptionDeadLine()
    {
        $nowDate = new DateTime("now");
        if ($this->subscribeAt) {

            $periodofvalidity = new DateTime($this->subscribeAt->format('Y/m/d'));
            $periodofvalidity->add(new DateInterval('P' . $this->subscriptionDuration . 'M'));

            /*$interval = $nowDate->diff($this->subscribeAt);
            //$interval = $this->periodofvalidity->diff($nowDate);
            if ($interval) {
                //return gettype($interval->format('d'));
                return $interval->format('%R%a jours'); // '+29 days'
                //return $interval->days; //Nombre de jour total de différence entre les dates 
                //return !$interval->invert; // 
            }
            return '';*/

            return $this->formatDateDiff($nowDate, $periodofvalidity); //
        } else {
            return 'Pas Encore abonné';
        }
    }

    public function getDeadLine()
    {
        if ($this->subscribeAt) {

            $nowDate = new DateTime("now");
            $this->periodofvalidity = new DateTime($this->subscribeAt->format('Y/m/d H:i:s'));
            $this->periodofvalidity->add(new DateInterval('P' . $this->subscriptionDuration . 'M'));
            $interval = $nowDate->diff($this->periodofvalidity);
            //$interval = $this->periodofvalidity->diff($nowDate);
            if ($interval) {
                //return gettype($interval->format('d'));
                //return $interval->format('%R%a days'); // '+29 days'
                return $interval->days; //Nombre de jour total de différence entre les dates 
                //return !$interval->invert; // 
                //return $this->isActivated;
            }
        } else {
            return 0;
        }
    }

    /**
     * A sweet interval formatting, will use the two biggest interval parts.
     * On small intervals, you get minutes and seconds.
     * On big intervals, you get months and days.
     * Only the two biggest parts are used.
     *
     * @param DateTime $start
     * @param DateTime|null $end
     * @return string
     */
    public function formatDateDiff($start, $end = null)
    {
        if (!($start instanceof DateTime)) {
            $start = new DateTime($start);
        }

        if ($end === null) {
            $end = new DateTime();
        }

        if (!($end instanceof DateTime)) {
            $end = new DateTime($start);
        }

        $interval = $end->diff($start);
        $doPlural = function ($nb, $str) {
            if ($str !== 'Mois') return $nb > 1 ? $str . 's' : $str;
            return $str;
        }; // adds plurals

        $format = array();
        if ($interval->y !== 0) {
            $format[] = "%y " . $doPlural($interval->y, "An");
        }
        if ($interval->m !== 0) {
            $format[] = "%m " . $doPlural($interval->m, "Mois");
        }
        if ($interval->d !== 0) {
            $format[] = "%d " . $doPlural($interval->d, "Jour");
        }
        if ($interval->h !== 0) {
            $format[] = "%h " . $doPlural($interval->h, "Heure");
        }
        if ($interval->i !== 0) {
            $format[] = "%i " . $doPlural($interval->i, "Minute");
        }
        if ($interval->s !== 0) {
            if (!count($format)) {
                return "less than a minute ago";
            } else {
                $format[] = "%s " . $doPlural($interval->s, "Seconde");
            }
        }

        // We use the two biggest parts
        if (count($format) > 1) {
            $format = array_shift($format) . " et " . array_shift($format);
        } else {
            $format = array_pop($format);
        }

        // Prepend 'since ' or whatever you like
        return $interval->format($format);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSocialReason(): ?string
    {
        return $this->socialReason;
    }

    public function setSocialReason(string $socialReason): self
    {
        $this->socialReason = $socialReason;

        return $this;
    }

    public function getNiu(): ?string
    {
        return $this->niu;
    }

    public function setNiu(?string $niu): self
    {
        $this->niu = $niu;

        return $this;
    }

    public function getRccm(): ?string
    {
        return $this->rccm;
    }

    public function setRccm(?string $rccm): self
    {
        $this->rccm = $rccm;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setEnterprise($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getEnterprise() === $this) {
                $user->setEnterprise(null);
            }
        }

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

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(?Subscription $subscription): self
    {
        $this->subscription = $subscription;

        return $this;
    }

    public function getSubscriptionDuration(): ?int
    {
        return $this->subscriptionDuration;
    }

    public function setSubscriptionDuration(?int $subscriptionDuration): self
    {
        $this->subscriptionDuration = $subscriptionDuration;

        return $this;
    }

    public function getSubscribeAt(): ?\DateTimeInterface
    {
        return $this->subscribeAt;
    }

    public function setSubscribeAt(?\DateTimeInterface $subscribeAt): self
    {
        $this->subscribeAt = $subscribeAt;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getTown(): ?string
    {
        return $this->town;
    }

    public function setTown(string $town): self
    {
        $this->town = $town;

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
     * Get the value of tarifs
     */
    public function getTarifs()
    {
        return $this->tarifs;
    }

    /**
     * Set the value of tarifs
     *
     * @return  self
     */
    public function setTarifs($tarifs)
    {
        $this->tarifs = $tarifs;

        return $this;
    }

    public function getIsActivated(): ?bool
    {
        $nowDate = new DateTime("now");
        if ($this->subscribeAt) {
            $this->periodofvalidity = new DateTime($this->subscribeAt->format('Y/m/d'));
            $this->periodofvalidity->add(new DateInterval('P' . $this->subscriptionDuration . 'M'));
            $interval = $nowDate->diff($this->periodofvalidity);
            //$interval = $this->periodofvalidity->diff($nowDate);
            if ($interval) {
                //return gettype($interval->format('d'));
                //return $interval->format('%R%a days');// '+29 days'
                //return $interval->days; //Nombre de jour total de différence entre les dates 
                $this->setIsActivated(!$interval->invert);
                //return !$interval->invert; // 
                return $this->isActivated;
            }
        } else {
            return false;
        }
    }

    public function setIsActivated(bool $isActivated): self
    {
        $this->isActivated = $isActivated;

        return $this;
    }

    /**
     * @return Collection|Office[]
     */
    public function getOffices(): Collection
    {
        return $this->offices;
    }

    public function addOffice(Office $office): self
    {
        if (!$this->offices->contains($office)) {
            $this->offices[] = $office;
            $office->setEnterprise($this);
        }

        return $this;
    }

    public function removeOffice(Office $office): self
    {
        if ($this->offices->removeElement($office)) {
            // set the owning side to null (unless already changed)
            if ($office->getEnterprise() === $this) {
                $office->setEnterprise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Team[]
     */
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(Team $team): self
    {
        if (!$this->teams->contains($team)) {
            $this->teams[] = $team;
            $team->setEnterprise($this);
        }

        return $this;
    }

    public function removeTeam(Team $team): self
    {
        if ($this->teams->removeElement($team)) {
            // set the owning side to null (unless already changed)
            if ($team->getEnterprise() === $this) {
                $team->setEnterprise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PointingLocation[]
     */
    public function getPointingLocations(): Collection
    {
        return $this->pointingLocations;
    }

    public function addPointingLocation(PointingLocation $pointingLocation): self
    {
        if (!$this->pointingLocations->contains($pointingLocation)) {
            $this->pointingLocations[] = $pointingLocation;
            $pointingLocation->setEnterprise($this);
        }

        return $this;
    }

    public function removePointingLocation(PointingLocation $pointingLocation): self
    {
        if ($this->pointingLocations->removeElement($pointingLocation)) {
            // set the owning side to null (unless already changed)
            if ($pointingLocation->getEnterprise() === $this) {
                $pointingLocation->setEnterprise(null);
            }
        }

        return $this;
    }

    public function getTimeZone(): ?float
    {
        return $this->timeZone;
    }

    public function setTimeZone(float $timeZone): self
    {
        $this->timeZone = $timeZone;

        return $this;
    }

    /**
     * @return Collection|Invoice[]
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices[] = $invoice;
            $invoice->setEnterprise($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->contains($invoice)) {
            $this->invoices->removeElement($invoice);
            // set the owning side to null (unless already changed)
            if ($invoice->getEnterprise() === $this) {
                $invoice->setEnterprise(null);
            }
        }

        return $this;
    }

    public function getRegisterBy(): ?User
    {
        return $this->registerBy;
    }

    public function setRegisterBy(?User $registerBy): self
    {
        $this->registerBy = $registerBy;

        return $this;
    }
}
