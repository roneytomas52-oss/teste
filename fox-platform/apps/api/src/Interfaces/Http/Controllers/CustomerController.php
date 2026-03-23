<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Controllers;

use FoxPlatform\Api\Application\Customer\CreateCustomerOrder;
use FoxPlatform\Api\Application\Customer\GetCustomerOrderDetail;
use FoxPlatform\Api\Application\Customer\GetCustomerOrders;
use FoxPlatform\Api\Application\Customer\GetCustomerProfile;
use FoxPlatform\Api\Application\Customer\UpdateCustomerProfile;
use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Interfaces\Http\Requests\CustomerOrderCreateRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\CustomerProfileUpdateRequest;
use FoxPlatform\Api\Interfaces\Http\Responses\ApiResponse;

class CustomerController
{
    public function __construct(
        private readonly GetCustomerProfile $getCustomerProfile,
        private readonly UpdateCustomerProfile $updateCustomerProfile,
        private readonly GetCustomerOrders $getCustomerOrders,
        private readonly GetCustomerOrderDetail $getCustomerOrderDetail,
        private readonly CreateCustomerOrder $createCustomerOrder,
        private readonly CustomerProfileUpdateRequest $customerProfileUpdateRequest,
        private readonly CustomerOrderCreateRequest $customerOrderCreateRequest
    ) {
    }

    public function profile(Request $request)
    {
        $auth = $request->attribute('auth');
        return ApiResponse::success(($this->getCustomerProfile)($auth['user_id']));
    }

    public function updateProfile(Request $request)
    {
        $auth = $request->attribute('auth');
        $validated = $this->customerProfileUpdateRequest->validate($request);

        return ApiResponse::success(
            ($this->updateCustomerProfile)($auth['user_id'], $validated),
            'Perfil do cliente atualizado com sucesso.'
        );
    }

    public function orders(Request $request)
    {
        $auth = $request->attribute('auth');
        return ApiResponse::success(($this->getCustomerOrders)($auth['user_id']));
    }

    public function orderDetail(Request $request)
    {
        $auth = $request->attribute('auth');
        return ApiResponse::success(
            ($this->getCustomerOrderDetail)($auth['user_id'], (string) $request->attribute('order_id'))
        );
    }

    public function createOrder(Request $request)
    {
        $auth = $request->attribute('auth');
        $validated = $this->customerOrderCreateRequest->validate($request);

        return ApiResponse::success(
            ($this->createCustomerOrder)($auth['user_id'], $validated),
            'Pedido do cliente criado com sucesso.'
        );
    }
}
