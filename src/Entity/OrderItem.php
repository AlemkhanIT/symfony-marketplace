<?php

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $orderr = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderr(): ?Order
    {
        return $this->orderr;
    }

    public function setOrderr(?Order $orderr): static
    {
        $this->orderr = $orderr;

        return $this;
    }
}
