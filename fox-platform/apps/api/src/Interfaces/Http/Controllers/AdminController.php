<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Controllers;

use FoxPlatform\Api\Application\Admin\GetAdminDashboard;
use FoxPlatform\Api\Application\Admin\GetAdminFinance;
use FoxPlatform\Api\Application\Admin\GetAdminDriverApprovals;
use FoxPlatform\Api\Application\Admin\GetAdminOrders;
use FoxPlatform\Api\Application\Admin\GetAdminOrderDetail;
use FoxPlatform\Api\Application\Admin\GetAdminPartnerApprovals;
use FoxPlatform\Api\Application\Admin\GetAdminSettings;
use FoxPlatform\Api\Application\Admin\GetAdminSupport;
use FoxPlatform\Api\Application\Admin\GetAdminSupportThread;
use FoxPlatform\Api\Application\Admin\UpdateAdminSettings;
use FoxPlatform\Api\Application\Admin\ReplyAdminSupportThread;
use FoxPlatform\Api\Application\Admin\UpdateAdminSupportTicketStatus;
use FoxPlatform\Api\Application\Admin\ApprovePartnerApproval;
use FoxPlatform\Api\Application\Admin\RejectPartnerApproval;
use FoxPlatform\Api\Application\Admin\ApproveDriverApproval;
use FoxPlatform\Api\Application\Admin\RejectDriverApproval;
use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Interfaces\Http\Responses\ApiResponse;
use FoxPlatform\Api\Interfaces\Http\Requests\AdminSupportTicketStatusUpdateRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\AdminSettingsUpdateRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\SupportMessageCreateRequest;

class AdminController
{
    public function __construct(
        private readonly GetAdminDashboard $getAdminDashboard,
        private readonly GetAdminFinance $getAdminFinance,
        private readonly GetAdminOrders $getAdminOrders,
        private readonly GetAdminOrderDetail $getAdminOrderDetail,
        private readonly GetAdminPartnerApprovals $getAdminPartnerApprovals,
        private readonly GetAdminDriverApprovals $getAdminDriverApprovals,
        private readonly GetAdminSupport $getAdminSupport,
        private readonly GetAdminSupportThread $getAdminSupportThread,
        private readonly GetAdminSettings $getAdminSettings,
        private readonly UpdateAdminSettings $updateAdminSettings,
        private readonly ReplyAdminSupportThread $replyAdminSupportThread,
        private readonly UpdateAdminSupportTicketStatus $updateAdminSupportTicketStatus,
        private readonly ApprovePartnerApproval $approvePartnerApproval,
        private readonly RejectPartnerApproval $rejectPartnerApproval,
        private readonly ApproveDriverApproval $approveDriverApproval,
        private readonly RejectDriverApproval $rejectDriverApproval,
        private readonly AdminSettingsUpdateRequest $adminSettingsUpdateRequest,
        private readonly AdminSupportTicketStatusUpdateRequest $adminSupportTicketStatusUpdateRequest,
        private readonly SupportMessageCreateRequest $supportMessageCreateRequest
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

    public function orderDetail(Request $request)
    {
        return ApiResponse::success(
            ($this->getAdminOrderDetail)((string) $request->attribute('order_id'))
        );
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

    public function supportThread(Request $request)
    {
        return ApiResponse::success(
            ($this->getAdminSupportThread)((string) $request->attribute('ticket_id'))
        );
    }

    public function replySupportThread(Request $request)
    {
        $validated = $this->supportMessageCreateRequest->validate($request);

        return ApiResponse::success(
            ($this->replyAdminSupportThread)(
                (string) $request->attribute('auth', [])['user_id'],
                (string) $request->attribute('ticket_id'),
                $validated
            ),
            'Mensagem registrada com sucesso.'
        );
    }

    public function updateSupportTicketStatus(Request $request)
    {
        $validated = $this->adminSupportTicketStatusUpdateRequest->validate($request);

        return ApiResponse::success(
            ($this->updateAdminSupportTicketStatus)(
                (string) $request->attribute('auth', [])['user_id'],
                (string) $request->attribute('ticket_id'),
                $validated
            ),
            'Status do chamado atualizado com sucesso.'
        );
    }

    public function settings(Request $request)
    {
        return ApiResponse::success(($this->getAdminSettings)());
    }

    public function updateSettings(Request $request)
    {
        $validated = $this->adminSettingsUpdateRequest->validate($request);

        return ApiResponse::success(
            ($this->updateAdminSettings)(
                (string) $request->attribute('auth', [])['user_id'],
                $validated['settings']
            ),
            'Configuracoes da plataforma atualizadas com sucesso.'
        );
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
