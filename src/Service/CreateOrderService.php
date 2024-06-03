<?php

namespace App\Service;

use App\Dto\OrderCreateDto;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;

class CreateOrderService
{

    public function __construct(
       private EntityManagerInterface $em
    )
    {
    }

    public function createOrder(OrderCreateDto $data)
    {
        try {
            $order = new Order($data);
            $this->em->persist($order);
            $this->em->flush();
            return $order;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}