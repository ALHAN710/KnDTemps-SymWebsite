<?php

namespace App\Entity;

use App\Repository\OfficeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OfficeRepository::class)
 */
class Office
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * Salaire horaire
     * @ORM\Column(type="float", nullable=true)
     */
    private $hourlySalary;

    /**
     * Salaire Mensuel
     * @ORM\Column(type="float", nullable=true)
     */
    private $monthlySalary;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="office")
     */
    private $employees;

    /**
     * @ORM\ManyToOne(targetEntity=Enterprise::class, inversedBy="offices")
     * @ORM\JoinColumn(nullable=false)
     */
    private $enterprise;

    public function __construct()
    {
        $this->employees = new ArrayCollection();
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

    public function getHourlySalary(): ?float
    {
        return $this->hourlySalary;
    }

    public function setHourlySalary(float $hourlySalary): self
    {
        $this->hourlySalary = $hourlySalary;

        return $this;
    }

    public function getMonthlySalary(): ?float
    {
        return $this->monthlySalary;
    }

    public function setMonthlySalary(?float $monthlySalary): self
    {
        $this->monthlySalary = $monthlySalary;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getEmployees(): Collection
    {
        return $this->employees;
    }

    public function addEmployee(User $employee): self
    {
        if (!$this->employees->contains($employee)) {
            $this->employees[] = $employee;
            $employee->setOffice($this);
        }

        return $this;
    }

    public function removeEmployee(User $employee): self
    {
        if ($this->employees->removeElement($employee)) {
            // set the owning side to null (unless already changed)
            if ($employee->getOffice() === $this) {
                $employee->setOffice(null);
            }
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
}
