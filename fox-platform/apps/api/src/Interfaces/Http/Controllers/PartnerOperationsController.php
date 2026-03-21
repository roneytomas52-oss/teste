<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Controllers;

use FoxPlatform\Api\Application\Partner\GetPartnerDashboard;
use FoxPlatform\Api\Application\Partner\GetPartnerFinance;
use FoxPlatform\Api\Application\Partner\GetPartnerNotifications;
use FoxPlatform\Api\Application\Partner\GetPartnerOrders;
use FoxPlatform\Api\Application\Partner\GetPartnerSupport;
use FoxPlatform\Api\Application\Partner\GetPartnerSupportThread;
use FoxPlatform\Api\Application\Partner\CreatePartnerSupportTicket;
use FoxPlatform\Api\Application\Partner\MarkPartnerNotificationRead;
use FoxPlatform\Api\Application\Partner\ReplyPartnerSupportThread;
use FoxPlatform\Api\Application\Partner\UpdatePartnerOrderStatus;
use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Interfaces\Http\Requests\PartnerOrderStatusUpdateRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\SupportMessageCreateRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\SupportTicketCreateRequest;
use FoxPlatform\Api\Interfaces\Http\Responses\ApiResponse;

class PartnerOperationsController
{
    public function __construct(
        private readonly GetPartnerDashboard $getPartnerDashboard,
        private readonly GetPartnerFinance $getPartnerFinance,
        private readonly GetPartnerOrders $getPartnerOrders,
        private readonly GetPartnerSupport $getPartnerSupport,
        private readonly GetPartnerSupportThread $getPartnerSupportThread,
        private readonly CreatePartnerSupportTicket $createPartnerSupportTicket,
        private readonly ReplyPartnerSupportThread $replyPartnerSupportThread,
        private readonly GetPartnerNotifications $getPartnerNotifications,
        private readonly MarkPartnerNotificationRead $markPartnerNotificationRead,
        private readonly UpdatePartnerOrderStatus $updatePartnerOrderStatus,
        private readonly PartnerOrderStatusUpdateRequest $partnerOrderStatusUpdateRequest,
        private readonly SupportTicketCreateRequest $supportTicketCreateRequest,
        private readonly SupportMessageCreateRequest $supportMessageCreateRequest
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

    public function finance(Request $request)
    {
        $auth = $request->attribute('auth');
        return ApiResponse::success(($this->getPartnerFinance)($auth['user_id']));
    }

    public function support(Request $request)
    {
        $auth = $request->attribute('auth');
        return ApiResponse::success(($this->getPartnerSupport)($auth['user_id']));
    }

    public function supportThread(Request $request)
    {
        $auth = $request->attribute('auth');

        return ApiResponse::success(
            ($this->getPartnerSupportThread)($auth['user_id'], (string) $request->attribute('ticket_id'))
        );
    }

    public function updateOrderStatus(Request $request)
    {
        $auth = $request->attribute('auth');
        $validated = $this->partnerOrderStatusUpdateRequest->validate($request);
        $orders = ($this->updatePartnerOrderStatus)($auth['user_id'], (string) $request->attribute('order_id'), $validated);

        return ApiResponse::success($orders, 'Status do pedido atualizado com sucesso.');
    }

    public function createSupportTicket(Request $request)
    {
        $auth = $request->attribute('auth');
        $validated = $this->supportTicketCreateRequest->validate($request);

        return ApiResponse::success(
            ($this->createPartnerSupportTicket)($auth['user_id'], $validated),
            'Chamado registrado com sucesso.'
        );
    }

    public function replySupportThread(Request $request)
    {
        $auth = $request->attribute('auth');
        $validated = $this->supportMessageCreateRequest->validate($request);

        return ApiResponse::success(
            ($this->replyPartnerSupportThread)($auth['user_id'], (string) $request->attribute('ticket_id'), $validated),
            'Mensagem registrada com sucesso.'
        );
    }

    public function notifications(Request $request)
    {
        $auth = $request->attribute('auth');
        return ApiResponse::success(($this->getPartnerNotifications)($auth['user_id']));
    }

    public function markNotificationRead(Request $request)
    {
        $auth = $request->attribute('auth');

        return ApiResponse::success(
            ($this->markPartnerNotificationRead)($auth['user_id'], (string) $request->attribute('notification_id')),
            'Notificacao marcada como lida.'
        );
    }
}
