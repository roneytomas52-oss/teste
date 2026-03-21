<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Controllers;

use FoxPlatform\Api\Application\Admin\GetAdminDashboard;
use FoxPlatform\Api\Application\Admin\GetAdminFinance;
use FoxPlatform\Api\Application\Admin\GetAdminDriverApprovals;
use FoxPlatform\Api\Application\Admin\GetAdminOrders;
use FoxPlatform\Api\Application\Admin\GetAdminPartnerApprovals;
use FoxPlatform\Api\Application\Admin\GetAdminSupport;
use FoxPlatform\Api\Application\Admin\ApprovePartnerApproval;
use FoxPlatform\Api\Application\Admin\RejectPartnerApproval;
use FoxPlatform\Api\Application\Admin\ApproveDriverApproval;
use FoxPlatform\Api\Application\Admin\RejectDriverApproval;
use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Interfaces\Http\Responses\ApiResponse;

class AdminController
{
    public function __construct(
        private readonly GetAdminDashboard $getAdminDashboard,
        private readonly GetAdminFinance $getAdminFinance,
        private readonly GetAdminOrders $getAdminOrders,
        private readonly GetAdminPartnerApprovals $getAdminPartnerApprovals,
        private readonly GetAdminDriverApprovals $getAdminDriverApprovals,
        private readonly GetAdminSupport $getAdminSupport,
        private readonly ApprovePartnerApproval $approvePartnerApproval,
        private readonly RejectPartnerApproval $rejectPartnerApproval,
        private readonly ApproveDriverApproval $approveDriverApproval,
        private readonly RejectDriverApproval $rejectDriverApproval
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

    public function finance(Request $request)
    {
        return ApiResponse::success(($this->getAdminFinance)());
    }

    public function partnerApprovals(Request $request)
    {
        return ApiResponse::success(($this->getAdminPartnerApprovals)());
    }

    public function driverApprovals(Request $request)
    {
        return ApiResponse::success(($this->getAdminDriverApprovals)());
    }

    public function support(Request $request)
    {
        return ApiResponse::success(($this->getAdminSupport)());
    }

    public function approvePartner(Request $request)
    {
        return ApiResponse::success(
            ($this->approvePartnerApproval)((string) $request->attribute('partner_id')),
            'Parceiro aprovado com sucesso.'
        );
    }

    public function rejectPartner(Request $request)
    {
        return ApiResponse::success(
            ($this->rejectPartnerApproval)((string) $request->attribute('partner_id')),
            'Parceiro movido para revisao manual.'
        );
    }

    public function approveDriver(Request $request)
    {
        return ApiResponse::success(
            ($this->approveDriverApproval)((string) $request->attribute('driver_id')),
            'Entregador aprovado com sucesso.'
        );
    }

    public function rejectDriver(Request $request)
    {
        return ApiResponse::success(
            ($this->rejectDriverApproval)((string) $request->attribute('driver_id')),
            'Entregador movido para revisao manual.'
        );
    }
}
