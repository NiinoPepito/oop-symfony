<?php

namespace App\Entity;

use App\Dto\OrderItemCreateDto;
use App\Repository\OrderItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $product = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $ordeer = null;

    #[ORM\Column]
    private ?int $price = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?string
    {
        return $this->product;
    }

    public function setProduct(string $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getOrdeer(): ?Order
    {
        return $this->ordeer;
    }

    public function setOrdeer(?Order $ordeer): static
    {
        $this->ordeer = $ordeer;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function __construct(Array $data)
    {
        $this->product = $data['product'];
        $this->quantity = $data['quantity'];
        $this->price = $data['price'];
    }
}
