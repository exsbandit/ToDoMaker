<?php

namespace App\Entity;

use App\Repository\DeveloperRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeveloperRepository::class)]
class Developer
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'integer')]
    private $efficiency;

    #[ORM\Column(type: 'integer')]
    private $weekly_hours = 45;

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

    public function getEfficiency(): ?int
    {
        return $this->efficiency;
    }

    public function setEfficiency(int $efficiency): self
    {
        $this->efficiency = $efficiency;
        return $this;
    }

    public function getHoursPerWeek(): ?int
    {
        return $this->weekly_hours;
    }

    public function setHoursPerWeek(int $hoursPerWeek): self
    {
        $this->weekly_hours = $hoursPerWeek;
        return $this;
    }
}