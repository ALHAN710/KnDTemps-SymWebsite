<?php

namespace App\Entity;

use DateTime;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true, nullable=true)
     * 
     * @Assert\Email(message="Veuillez renseigner une adresse email valide !")
     */
    private $email; //@Assert\NotBlank()

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez renseigner votre prénom")
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veuillez renseigner votre nom")
     */
    private $lastName;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Assert\Length(min=8, minMessage="Votre mot de passe doit contenir au moins 8 caractères !")
     */
    private $hash;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(message="Please enter your primary phone number")
     */
    private $phoneNumber;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     */
    private $countryCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $verificationCode;

    /**
     * @ORM\Column(type="boolean")
     */
    private $verified;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $avatar;

    /**
     * Champ crée pour confirmation de mot de pass dans le formulaire mais non utilisé dans la BDD
     *
     * @Assert\EqualTo(propertyPath="hash", message="Vous n'avez pas correctement confirmé votre mot de passe")
     */
    private $passwordConfirm;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Role", mappedBy="users")
     */
    private $userRoles;

    /**
     * Champ crée pour attribuer un rôle à l'utilisateur mais non utilisé dans la BDD
     *
     * 
     */
    private $role;

    /**
     * Titre de l'employé (M., Mme, Mr., Dr., PhD)
     * @ORM\Column(type="string", length=10)
     */
    private $grade;

    /**
     * Salaire horaire
     */
    private $hourlyWage; //@ORM\Column(type="float")

    /**
     * Salaire Mensuel
     */
    private $monthlySalary; //@ORM\Column(type="float", nullable=true)

    /**
     * Attribut de l'employé (Leader <=> chef d'équipe ou Subordinate <=> subordonné)
     * 
     */
    private $attribut; //@ORM\Column(type="string", length=20, nullable=true)

    /**
     * Temps de travail par jour
     * @ORM\Column(type="integer", nullable=true)
     */
    private $wtperday;

    /**
     * Matricule de l'employé
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $registrationNumber;

    /**
     * Commission de l'employé
     * @ORM\Column(type="float", nullable=true)
     */
    private $commission;

    /**
     * Equipes sous la responsabilité de l'employé
     * @ORM\OneToMany(targetEntity=Team::class, mappedBy="responsible", orphanRemoval=true)
     */
    private $teams;

    /**
     * @ORM\OneToMany(targetEntity=Pointing::class, mappedBy="employee", orphanRemoval=true)
     */
    private $pointings;

    /**
     * @ORM\ManyToOne(targetEntity=Office::class, inversedBy="employees")
     * @ORM\JoinColumn(nullable=true)
     */
    private $office;

    /**
     * Statut de l'employé (In Function, Dismissed) ==> (en fonction, licencié)
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $statut;

    /**
     * @ORM\ManyToOne(targetEntity=Enterprise::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=true)
     */
    private $enterprise;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $hiringAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $bornAt;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $sex;

    /**
     * Equipe à laquelle appartient l'employé
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="users")
     */
    private $team;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dismissedAt;

    /**
     * @ORM\ManyToOne(targetEntity=PointingLocation::class, inversedBy="users")
     */
    private $pointingLocation;

    /**
     * @ORM\OneToMany(targetEntity=Invoice::class, mappedBy="user")
     */
    private $invoices;

    private $user_;

    /**
     * @ORM\OneToMany(targetEntity=Enterprise::class, mappedBy="registerBy")
     */
    private $enterprises;

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
     * Permet d'initialiser l'état de vérification !
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * 
     * @return void
     */
    public function initializeVerified()
    {
        if (empty($this->verified)) {
            $this->verified = true;
        }
    }

    public function __construct()
    {
        $this->teams = new ArrayCollection();
        $this->pointings = new ArrayCollection();
        $this->userRoles = new ArrayCollection();
        $this->enterprises = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getAge()
    {
        $now = new DateTime('now');

        return $this->formatDateDiff($now, $this->bornAt); //
    }

    public function getSeniority()
    {
        $now = new DateTime('now');

        return $this->formatDateDiff($now, $this->hiringAt); //
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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

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

    public function getHash(): string
    {
        return (string) $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getPasswordConfirm(): ?string
    {
        return $this->passwordConfirm;
    }

    public function setPasswordConfirm(string $passwordConfirm): self
    {
        $this->passwordConfirm = $passwordConfirm;

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

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }


    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getVerificationCode(): ?string
    {
        return $this->verificationCode;
    }

    public function setVerificationCode(?string $verificationCode): self
    {
        $this->verificationCode = $verificationCode;

        return $this;
    }

    public function getVerified(): ?bool
    {
        return $this->verified;
    }

    public function setVerified(bool $verified): self
    {
        $this->verified = $verified;

        return $this;
    }

    public function getGrade(): ?string
    {
        return $this->grade;
    }

    public function setGrade(string $grade): self
    {
        $this->grade = $grade;

        return $this;
    }

    public function getAttribut(): ?string
    {
        return $this->attribut;
    }

    public function setAttribut(string $attribut): self
    {
        $this->attribut = $attribut;

        return $this;
    }

    public function getWtperday(): ?int
    {
        return $this->wtperday;
    }

    public function setWtperday(?int $wtperday): self
    {
        $this->wtperday = $wtperday;

        return $this;
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(?string $registrationNumber): self
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    public function getCommission(): ?float
    {
        return $this->commission;
    }

    public function setCommission(?float $commission): self
    {
        $this->commission = $commission;

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
            $team->setResponsible($this);
        }

        return $this;
    }

    public function removeTeam(Team $team): self
    {
        if ($this->teams->removeElement($team)) {
            // set the owning side to null (unless already changed)
            if ($team->getResponsible() === $this) {
                $team->setResponsible(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Pointing[]
     */
    public function getPointings(): Collection
    {
        return $this->pointings;
    }

    public function addPointing(Pointing $pointing): self
    {
        if (!$this->pointings->contains($pointing)) {
            $this->pointings[] = $pointing;
            $pointing->setEmployee($this);
        }

        return $this;
    }

    public function removePointing(Pointing $pointing): self
    {
        if ($this->pointings->removeElement($pointing)) {
            // set the owning side to null (unless already changed)
            if ($pointing->getEmployee() === $this) {
                $pointing->setEmployee(null);
            }
        }

        return $this;
    }

    public function getOffice(): ?Office
    {
        return $this->office;
    }

    public function setOffice(?Office $office): self
    {
        $this->office = $office;

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

    /**
     * @return Collection|Role[]
     */
    public function getUserRoles(): Collection
    {
        return $this->userRoles;
    }

    public function addUserRole(Role $userRole): self
    {
        if (!$this->userRoles->contains($userRole)) {
            $this->userRoles[] = $userRole;
            $userRole->addUser($this);
        }

        return $this;
    }

    public function removeUserRole(Role $userRole): self
    {
        if ($this->userRoles->contains($userRole)) {
            $this->userRoles->removeElement($userRole);
            $userRole->removeUser($this);
        }

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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        /*$roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);*/

        $roles = $this->userRoles->map(function ($role) {
            return $role->getTitle();
        })->toArray();

        $roles[] = 'ROLE_USER';

        return $roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->hash;
    }


    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getHiringAt(): ?\DateTimeInterface
    {
        return $this->hiringAt;
    }

    public function setHiringAt(?\DateTimeInterface $hiringAt): self
    {
        $this->hiringAt = $hiringAt;

        return $this;
    }

    public function getBornAt(): ?\DateTimeInterface
    {
        return $this->bornAt;
    }

    public function setBornAt(?\DateTimeInterface $bornAt): self
    {
        $this->bornAt = $bornAt;

        return $this;
    }

    public function getSex(): ?string
    {
        return $this->sex;
    }

    public function setSex(string $sex): self
    {
        $this->sex = $sex;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getDismissedAt(): ?\DateTimeInterface
    {
        return $this->dismissedAt;
    }

    public function setDismissedAt(?\DateTimeInterface $dismissedAt): self
    {
        $this->dismissedAt = $dismissedAt;

        return $this;
    }

    public function getPointingLocation(): ?PointingLocation
    {
        return $this->pointingLocation;
    }

    public function setPointingLocation(?PointingLocation $pointingLocation): self
    {
        $this->pointingLocation = $pointingLocation;

        return $this;
    }

    /**
     * Get the value of user_
     */
    public function getUser_()
    {
        return $this->user_;
    }

    /**
     * Set the value of user_
     *
     * @return  self
     */
    public function setUser_($user_)
    {
        $this->user_ = $user_;

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
            $invoice->setUser($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->contains($invoice)) {
            $this->invoices->removeElement($invoice);
            // set the owning side to null (unless already changed)
            if ($invoice->getUser() === $this) {
                $invoice->setUser(null);
            }
        }

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
            $enterprise->setRegisterBy($this);
        }

        return $this;
    }

    public function removeEnterprise(Enterprise $enterprise): self
    {
        if ($this->enterprises->removeElement($enterprise)) {
            // set the owning side to null (unless already changed)
            if ($enterprise->getRegisterBy() === $this) {
                $enterprise->setRegisterBy(null);
            }
        }

        return $this;
    }
}
