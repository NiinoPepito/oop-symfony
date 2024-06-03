<?php

namespace App\Entity;

use App\Dto\OrderCreateDto;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    const OrderStatus = [
        'CREATED' => 'Créée',
        'PAID' => 'Payée',
        'CANCELED' => 'Annulée'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(length: 255)]
    private ?string $customer = null;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'ordeer', cascade: ['persist', 'remove'])]
    private Collection $items;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column]
    private ?int $total = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $paidAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shippingAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shippingMethod = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $invoiceAddress = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $shippingMethodSetAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $invoiceAddressSetAt = null;

    public function __construct(OrderCreateDto $data)
    {
        $this->customer = $data->customer;
        $this->createdAt = new \DateTime();
        $this->updatedAt = null;
        $this->status = self::OrderStatus['CREATED'];
        $this->total = 0;
        $this->paidAt = null;
        $this->shippingAddress = null;
        $this->shippingMethod = null;
        $this->invoiceAddress = null;
        $this->shippingMethodSetAt = null;
        $this->invoiceAddressSetAt = null;

        $this->items = new ArrayCollection();

        foreach ($data->items as $item) {
            $existingItem = $this->items->filter(function (OrderItem $i) use ($item) {
                return $i->getProduct() === $item['product'];
            })->first();

            if ($existingItem) {
                $existingItem->setQuantity($existingItem->getQuantity() + $item['quantity']);
                $this->total += $this->calculateTotal($item['quantity'], $item['price']);
            } else {
                $orderItem = new OrderItem($item);
                $this->addItem($orderItem);
                $this->total += $this->calculateTotal($orderItem->getQuantity(), $orderItem->getPrice());
            }
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCustomer(): ?string
    {
        return $this->customer;
    }

    public function setCustomer(string $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setOrdeer($this);
        }

        return $this;
    }

    public function removeItem(OrderItem $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getOrdeer() === $this) {
                $item->setOrdeer(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(int $total): static
    {
        $this->total = $total;

        return $this;
    }

    public function getPaidAt(): ?\DateTimeInterface
    {
        return $this->paidAt;
    }

    public function setPaidAt(\DateTimeInterface $paidAt): static
    {
        $this->paidAt = $paidAt;

        return $this;
    }

    public function getShippingAddress(): ?string
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress(string $shippingAddress): static
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    public function getShippingMethod(): ?string
    {
        return $this->shippingMethod;
    }

    public function setShippingMethod(string $shippingMethod): static
    {
        $this->shippingMethod = $shippingMethod;

        return $this;
    }

    public function getInvoiceAddress(): ?string
    {
        return $this->invoiceAddress;
    }

    public function setInvoiceAddress(string $invoiceAddress): static
    {
        $this->invoiceAddress = $invoiceAddress;

        return $this;
    }

    public function getShippingMethodSetAt(): ?\DateTimeInterface
    {
        return $this->shippingMethodSetAt;
    }

    public function setShippingMethodSetAt(\DateTimeInterface $shippingMethodSetAt): static
    {
        $this->shippingMethodSetAt = $shippingMethodSetAt;

        return $this;
    }

    public function getInvoiceAddressSetAt(): ?\DateTimeInterface
    {
        return $this->invoiceAddressSetAt;
    }

    public function setInvoiceAddressSetAt(\DateTimeInterface $invoiceAddressSetAt): static
    {
        $this->invoiceAddressSetAt = $invoiceAddressSetAt;

        return $this;
    }

    public function pay(): void
    {
        $this->paidAt = new \DateTime();
        $this->status = self::OrderStatus['PAID'];
        $this->updatedAt = new \DateTime();
    }

    private function calculateTotal(int $quantity, int $price): int
    {
        return $quantity * $price;
    }
}
