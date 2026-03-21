<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Controllers;

use FoxPlatform\Api\Application\Admin\GetAdminDashboard;
use FoxPlatform\Api\Application\Admin\GetAdminDriverApprovals;
use FoxPlatform\Api\Application\Admin\GetAdminOrders;
use FoxPlatform\Api\Application\Admin\GetAdminPartnerApprovals;
use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Interfaces\Http\Responses\ApiResponse;

class AdminController
{
    public function __construct(
        private readonly GetAdminDashboard $getAdminDashboard,
        private readonly GetAdminOrders $getAdminOrders,
        private readonly GetAdminPartnerApprovals $getAdminPartnerApprovals,
        private readonly GetAdminDriverApprovals $getAdminDriverApprovals
    ) {
    }

    public function dashboard(Request $request)
    {
        return ApiResponse::success(($this->getAdminDashboard)());
    }

    public function orders(Request $request)
    {
        return ApiResponse::success(($this->getAdminOrders)());
    }

    public function partnerApprovals(Request $request)
    {
        return ApiResponse::success(($this->getAdminPartnerApprovals)());
    }

    public function driverApprovals(Request $request)
    {
        return ApiResponse::success(($this->getAdminDriverApprovals)());
    }
}
