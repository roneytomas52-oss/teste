<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Controllers;

use FoxPlatform\Api\Application\Driver\GetDriverAvailability;
use FoxPlatform\Api\Application\Driver\GetDriverDashboard;
use FoxPlatform\Api\Application\Driver\GetDriverDocuments;
use FoxPlatform\Api\Application\Driver\GetDriverEarnings;
use FoxPlatform\Api\Application\Driver\GetDriverNotifications;
use FoxPlatform\Api\Application\Driver\GetDriverProfile;
use FoxPlatform\Api\Application\Driver\GetDriverSupport;
use FoxPlatform\Api\Application\Driver\GetDriverSupportThread;
use FoxPlatform\Api\Application\Driver\CreateDriverSupportTicket;
use FoxPlatform\Api\Application\Driver\MarkDriverNotificationRead;
use FoxPlatform\Api\Application\Driver\ReplyDriverSupportThread;
use FoxPlatform\Api\Application\Driver\UpdateDriverProfile;
use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Interfaces\Http\Requests\DriverProfileUpdateRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\SupportMessageCreateRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\SupportTicketCreateRequest;
use FoxPlatform\Api\Interfaces\Http\Responses\ApiResponse;

class DriverController
{
    public function __construct(
        private readonly GetDriverDashboard $getDriverDashboard,
        private readonly GetDriverProfile $getDriverProfile,
        private readonly UpdateDriverProfile $updateDriverProfile,
        private readonly GetDriverEarnings $getDriverEarnings,
        private readonly GetDriverAvailability $getDriverAvailability,
        private readonly GetDriverDocuments $getDriverDocuments,
        private readonly GetDriverSupport $getDriverSupport,
        private readonly GetDriverSupportThread $getDriverSupportThread,
        private readonly CreateDriverSupportTicket $createDriverSupportTicket,
        private readonly ReplyDriverSupportThread $replyDriverSupportThread,
        private readonly GetDriverNotifications $getDriverNotifications,
        private readonly MarkDriverNotificationRead $markDriverNotificationRead,
        private readonly DriverProfileUpdateRequest $driverProfileUpdateRequest,
        private readonly SupportTicketCreateRequest $supportTicketCreateRequest,
        private readonly SupportMessageCreateRequest $supportMessageCreateRequest
    ) {
    }

    public function dashboard(Request $request)
    {
        $auth = $request->attribute('auth');
        return ApiResponse::success(($this->getDriverDashboard)($auth['user_id']));
    }

    public function profile(Request $request)
    {
        $auth = $request->attribute('auth');
        return ApiResponse::success(($this->getDriverProfile)($auth['user_id']));
    }

    public function updateProfile(Request $request)
    {
        $auth = $request->attribute('auth');
        $validated = $this->driverProfileUpdateRequest->validate($request);

        return ApiResponse::success(
            ($this->updateDriverProfile)($auth['user_id'], $validated),
            'Perfil do entregador atualizado com sucesso.'
        );
    }

    public function earnings(Request $request)
    {
        $auth = $request->attribute('auth');
        return ApiResponse::success(($this->getDriverEarnings)($auth['user_id']));
    }

    public function availability(Request $request)
    {
        $auth = $request->attribute('auth');
        return ApiResponse::success(($this->getDriverAvailability)($auth['user_id']));
    }

    public function documents(Request $request)
    {
        $auth = $request->attribute('auth');
        return ApiResponse::success(($this->getDriverDocuments)($auth['user_id']));
    }

    public function support(Request $request)
    {
        $auth = $request->attribute('auth');
        return ApiResponse::success(($this->getDriverSupport)($auth['user_id']));
    }

    public function supportThread(Request $request)
    {
        $auth = $request->attribute('auth');
        return ApiResponse::success(
            ($this->getDriverSupportThread)($auth['user_id'], (string) $request->attribute('ticket_id'))
        );
    }

    public function createSupportTicket(Request $request)
    {
        $auth = $request->attribute('auth');
        $validated = $this->supportTicketCreateRequest->validate($request);

        return ApiResponse::success(
            ($this->createDriverSupportTicket)($auth['user_id'], $validated),
            'Chamado registrado com sucesso.'
        );
    }

    public function replySupportThread(Request $request)
    {
        $auth = $request->attribute('auth');
        $validated = $this->supportMessageCreateRequest->validate($request);

        return ApiResponse::success(
            ($this->replyDriverSupportThread)($auth['user_id'], (string) $request->attribute('ticket_id'), $validated),
            'Mensagem registrada com sucesso.'
        );
    }

    public function notifications(Request $request)
    {
        $auth = $request->attribute('auth');
        return ApiResponse::success(($this->getDriverNotifications)($auth['user_id']));
    }

    public function markNotificationRead(Request $request)
    {
        $auth = $request->attribute('auth');
        return ApiResponse::success(
            ($this->markDriverNotificationRead)($auth['user_id'], (string) $request->attribute('notification_id')),
            'Notificacao marcada como lida.'
        );
    }
}
