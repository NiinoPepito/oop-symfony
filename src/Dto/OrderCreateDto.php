<?php

namespace App\Dto;

class OrderCreateDto
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */
    public $customer;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("array")
     * @Assert\All({
     *     @Assert\Type("object"),
     *     @Assert\Valid()
     * })
     */
    public $items;

    public function __construct(mixed $data = [])
    {
        $this->customer = $data['customer'] ?? null;
        $this->items = $data['items'] ?? [];
    }
}
