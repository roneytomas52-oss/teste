<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Controllers;

use FoxPlatform\Api\Application\Auth\LoginUser;
use FoxPlatform\Api\Application\Auth\LogoutUser;
use FoxPlatform\Api\Application\Auth\RefreshToken;
use FoxPlatform\Api\Application\Auth\RequestPasswordReset;
use FoxPlatform\Api\Application\Auth\ResetPassword;
use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Infrastructure\Support\ApiException;
use FoxPlatform\Api\Interfaces\Http\Requests\ForgotPasswordRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\LoginRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\ResetPasswordRequest;
use FoxPlatform\Api\Interfaces\Http\Responses\ApiResponse;

class AuthController
{
    public function __construct(
        private readonly LoginUser $loginUser,
        private readonly LogoutUser $logoutUser,
        private readonly RefreshToken $refreshToken,
        private readonly RequestPasswordReset $requestPasswordReset,
        private readonly ResetPassword $resetPassword,
        private readonly LoginRequest $loginRequest,
        private readonly ForgotPasswordRequest $forgotPasswordRequest,
        private readonly ResetPasswordRequest $resetPasswordRequest
    ) {
    }

    public function login(Request $request)
    {
        $validated = $this->loginRequest->validate($request);
        $payload = ($this->loginUser)(
            $validated['input'],
            [
                'device_name' => (string) $request->header('x-device-name', 'web'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => (string) $request->header('user-agent', ''),
            ]
        );

        return ApiResponse::success($payload->toArray(), 'Login realizado com sucesso.');
    }

    public function logout(Request $request)
    {
        $refreshToken = trim((string) $request->input('refresh_token', ''));
        if ($refreshToken === '') {
            throw new ApiException(422, 'VALIDATION_ERROR', 'Refresh token e obrigatorio para logout.');
        }

        ($this->logoutUser)($refreshToken);

        return ApiResponse::success([], 'Sessao encerrada com sucesso.');
    }

    public function refresh(Request $request)
    {
        $refreshToken = trim((string) $request->input('refresh_token', ''));
        $guard = trim((string) $request->input('guard', ''));

        if ($refreshToken === '') {
            throw new ApiException(422, 'VALIDATION_ERROR', 'Refresh token e obrigatorio.');
        }

        $payload = ($this->refreshToken)(
            $refreshToken,
            [
                'guard' => $guard !== '' ? $guard : null,
                'device_name' => (string) $request->header('x-device-name', 'web'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => (string) $request->header('user-agent', ''),
            ]
        );

        return ApiResponse::success($payload->toArray(), 'Token renovado com sucesso.');
    }

    public function forgotPassword(Request $request)
    {
        $validated = $this->forgotPasswordRequest->validate($request);
        $result = ($this->requestPasswordReset)($validated['email']);

        return ApiResponse::success($result, 'Solicitacao de redefinicao registrada.');
    }

    public function resetPassword(Request $request)
    {
        $validated = $this->resetPasswordRequest->validate($request);
        ($this->resetPassword)(
            $validated['email'],
            $validated['token'],
            $validated['password']
        );

        return ApiResponse::success([], 'Senha redefinida com sucesso.');
    }
}
