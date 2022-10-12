<?php

namespace App\Entity;

use App\Repository\TestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TestRepository::class)]
class Test
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $f = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getF(): ?float
    {
        return $this->f;
    }

    public function setF(float $f): self
    {
        $this->f = $f;

        return $this;
    }
}
