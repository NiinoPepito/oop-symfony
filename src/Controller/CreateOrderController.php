<?php

namespace App\Controller;

use App\Dto\OrderCreateDto;
use App\Entity\Order;
use App\Service\CreateOrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateOrderController extends AbstractController
{

    public function __construct(
        private CreateOrderService $createOrderService,
        private ValidatorInterface $validator


    )
    {
    }

    #[Route('/order', name: 'order-create', methods: ['POST'])]
    public function createOrder(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $dto = new OrderCreateDto($data);

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        $order = $this->createOrderService->createOrder($dto);
        return new JsonResponse($order, 201);
    }

}