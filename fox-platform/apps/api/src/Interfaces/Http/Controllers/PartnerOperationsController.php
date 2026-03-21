<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Controllers;

use FoxPlatform\Api\Application\Partner\GetPartnerDashboard;
use FoxPlatform\Api\Application\Partner\GetPartnerOrders;
use FoxPlatform\Api\Application\Partner\UpdatePartnerOrderStatus;
use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Interfaces\Http\Requests\PartnerOrderStatusUpdateRequest;
use FoxPlatform\Api\Interfaces\Http\Responses\ApiResponse;

class PartnerOperationsController
{
    public function __construct(
        private readonly GetPartnerDashboard $getPartnerDashboard,
        private readonly GetPartnerOrders $getPartnerOrders,
        private readonly UpdatePartnerOrderStatus $updatePartnerOrderStatus,
        private readonly PartnerOrderStatusUpdateRequest $partnerOrderStatusUpdateRequest
    ) {
    }

    public function dashboard(Request $request)
    {
        $auth = $request->attribute('auth');
        return ApiResponse::success(($this->getPartnerDashboard)($auth['user_id']));
    }

    public function orders(Request $request)
    {
        $auth = $request->attribute('auth');
        return ApiResponse::success(($this->getPartnerOrders)($auth['user_id']));
    }

    public function updateOrderStatus(Request $request)
    {
        $auth = $request->attribute('auth');
        $validated = $this->partnerOrderStatusUpdateRequest->validate($request);
        $orders = ($this->updatePartnerOrderStatus)($auth['user_id'], (string) $request->attribute('order_id'), $validated);

        return ApiResponse::success($orders, 'Status do pedido atualizado com sucesso.');
    }
}
