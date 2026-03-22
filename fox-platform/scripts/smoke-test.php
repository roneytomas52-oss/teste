<?php

declare(strict_types=1);

final class SmokeTestFailure extends RuntimeException
{
}

$baseUrl = rtrim($argv[1] ?? getenv('FOX_PLATFORM_BASE_URL') ?: 'http://127.0.0.1:8099', '/');
$timestamp = (string) time();
$failures = [];

function writeLine(string $message): void
{
    echo $message . PHP_EOL;
}

function fail(string $label, string $message): never
{
    throw new SmokeTestFailure(sprintf('[%s] %s', $label, $message));
}

function request(string $method, string $url, ?array $body = null, array $headers = []): array
{
    $defaultHeaders = [
        'Accept: application/json',
        'X-Device-Name: smoke-test',
    ];

    if ($body !== null) {
        $defaultHeaders[] = 'Content-Type: application/json';
    }

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array_merge($defaultHeaders, $headers),
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HEADER => true,
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        $raw = curl_exec($ch);
        if ($raw === false) {
            $message = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException($message);
        }

        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $responseBody = substr($raw, $headerSize);
        curl_close($ch);

        return [
            'status' => $status,
            'body' => $responseBody,
            'json' => json_decode($responseBody, true),
        ];
    }

    $context = stream_context_create([
        'http' => [
            'method' => strtoupper($method),
            'header' => implode("\r\n", array_merge($defaultHeaders, $headers)),
            'content' => $body !== null
                ? json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : null,
            'ignore_errors' => true,
            'timeout' => 20,
        ],
    ]);

    $responseBody = @file_get_contents($url, false, $context);
    $responseHeaders = $http_response_header ?? [];
    $status = 0;
    foreach ($responseHeaders as $header) {
        if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $header, $matches)) {
            $status = (int) $matches[1];
            break;
        }
    }

    return [
        'status' => $status,
        'body' => $responseBody === false ? '' : $responseBody,
        'json' => json_decode($responseBody === false ? '' : $responseBody, true),
    ];
}

function expectSuccess(string $label, array $response): array
{
    if (($response['status'] ?? 0) < 200 || ($response['status'] ?? 0) >= 300) {
        fail($label, sprintf('HTTP %d returned: %s', $response['status'] ?? 0, $response['body'] ?? ''));
    }

    if (!is_array($response['json'] ?? null)) {
        fail($label, 'Response body is not valid JSON.');
    }

    if (($response['json']['success'] ?? false) !== true) {
        fail($label, sprintf('Application returned failure: %s', json_encode($response['json'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
    }

    writeLine(sprintf('[ok] %s', $label));

    return $response['json']['data'] ?? [];
}

function bearer(string $token): array
{
    return ['Authorization: Bearer ' . $token];
}

try {
    $health = expectSuccess('health', request('GET', $baseUrl . '/health'));

    $adminLogin = expectSuccess(
        'auth.login.admin',
        request('POST', $baseUrl . '/api/v1/auth/login', [
            'email' => 'admin@foxplatform.com',
            'password' => 'password',
            'guard' => 'admin',
        ])
    );
    $partnerLogin = expectSuccess(
        'auth.login.partner',
        request('POST', $baseUrl . '/api/v1/auth/login', [
            'email' => 'parceiro@foxdelivery.com.br',
            'password' => 'password',
            'guard' => 'partner',
        ])
    );
    $driverLogin = expectSuccess(
        'auth.login.driver',
        request('POST', $baseUrl . '/api/v1/auth/login', [
            'email' => 'entregador@foxdelivery.com.br',
            'password' => 'password',
            'guard' => 'driver',
        ])
    );

    expectSuccess('auth.me.admin', request('GET', $baseUrl . '/api/v1/auth/me', null, bearer($adminLogin['access_token'])));
    expectSuccess('auth.me.partner', request('GET', $baseUrl . '/api/v1/auth/me', null, bearer($partnerLogin['access_token'])));
    expectSuccess('auth.me.driver', request('GET', $baseUrl . '/api/v1/auth/me', null, bearer($driverLogin['access_token'])));

    $partnerProfile = expectSuccess('partner.profile', request('GET', $baseUrl . '/api/v1/partner/profile', null, bearer($partnerLogin['access_token'])));
    $partnerStore = expectSuccess('partner.store', request('GET', $baseUrl . '/api/v1/partner/store', null, bearer($partnerLogin['access_token'])));
    $partnerDashboard = expectSuccess('partner.dashboard', request('GET', $baseUrl . '/api/v1/partner/dashboard', null, bearer($partnerLogin['access_token'])));
    $partnerCatalog = expectSuccess('partner.catalog', request('GET', $baseUrl . '/api/v1/partner/catalog/products', null, bearer($partnerLogin['access_token'])));
    $partnerOrders = expectSuccess('partner.orders', request('GET', $baseUrl . '/api/v1/partner/orders', null, bearer($partnerLogin['access_token'])));
    $partnerFinance = expectSuccess('partner.finance', request('GET', $baseUrl . '/api/v1/partner/finance/summary', null, bearer($partnerLogin['access_token'])));
    $partnerTeam = expectSuccess('partner.team', request('GET', $baseUrl . '/api/v1/partner/team', null, bearer($partnerLogin['access_token'])));
    $partnerSupport = expectSuccess('partner.support', request('GET', $baseUrl . '/api/v1/partner/support', null, bearer($partnerLogin['access_token'])));
    $partnerNotifications = expectSuccess('partner.notifications', request('GET', $baseUrl . '/api/v1/partner/notifications', null, bearer($partnerLogin['access_token'])));
    $firstPartnerOrderId = $partnerOrders['orders'][0]['order_id'] ?? null;

    if ($firstPartnerOrderId) {
        expectSuccess(
            'partner.order-detail',
            request('GET', $baseUrl . '/api/v1/partner/orders/' . $firstPartnerOrderId, null, bearer($partnerLogin['access_token']))
        );
    }

    if (!empty($partnerNotifications['items'][0]['id'] ?? null)) {
        expectSuccess(
            'partner.notifications.read',
            request(
                'POST',
                $baseUrl . '/api/v1/partner/notifications/' . $partnerNotifications['items'][0]['id'] . '/read',
                null,
                bearer($partnerLogin['access_token'])
            )
        );
    }

    $adminDashboard = expectSuccess('admin.dashboard', request('GET', $baseUrl . '/api/v1/admin/dashboard', null, bearer($adminLogin['access_token'])));
    $adminSettings = expectSuccess('admin.settings.get', request('GET', $baseUrl . '/api/v1/admin/settings', null, bearer($adminLogin['access_token'])));
    expectSuccess(
        'admin.settings.put',
        request('PUT', $baseUrl . '/api/v1/admin/settings', $adminSettings, bearer($adminLogin['access_token']))
    );
    expectSuccess('admin.support', request('GET', $baseUrl . '/api/v1/admin/support/queue', null, bearer($adminLogin['access_token'])));
    $adminOrders = expectSuccess('admin.orders', request('GET', $baseUrl . '/api/v1/admin/orders', null, bearer($adminLogin['access_token'])));
    expectSuccess('admin.approvals.partners', request('GET', $baseUrl . '/api/v1/admin/approvals/partners', null, bearer($adminLogin['access_token'])));
    expectSuccess('admin.approvals.drivers', request('GET', $baseUrl . '/api/v1/admin/approvals/drivers', null, bearer($adminLogin['access_token'])));
    $firstAdminOrderId = $adminOrders['items'][0]['order_id'] ?? null;

    if ($firstAdminOrderId) {
        expectSuccess(
            'admin.order-detail',
            request('GET', $baseUrl . '/api/v1/admin/orders/' . $firstAdminOrderId, null, bearer($adminLogin['access_token']))
        );
    }

    expectSuccess('driver.dashboard', request('GET', $baseUrl . '/api/v1/driver/dashboard', null, bearer($driverLogin['access_token'])));
    expectSuccess('driver.profile', request('GET', $baseUrl . '/api/v1/driver/profile', null, bearer($driverLogin['access_token'])));
    expectSuccess('driver.earnings', request('GET', $baseUrl . '/api/v1/driver/earnings', null, bearer($driverLogin['access_token'])));
    expectSuccess('driver.documents', request('GET', $baseUrl . '/api/v1/driver/documents', null, bearer($driverLogin['access_token'])));
    expectSuccess('driver.support', request('GET', $baseUrl . '/api/v1/driver/support', null, bearer($driverLogin['access_token'])));
    $driverNotifications = expectSuccess('driver.notifications', request('GET', $baseUrl . '/api/v1/driver/notifications', null, bearer($driverLogin['access_token'])));
    if (!empty($driverNotifications['items'][0]['id'] ?? null)) {
        expectSuccess(
            'driver.notifications.read',
            request(
                'POST',
                $baseUrl . '/api/v1/driver/notifications/' . $driverNotifications['items'][0]['id'] . '/read',
                null,
                bearer($driverLogin['access_token'])
            )
        );
    }

    expectSuccess('public.categories', request('GET', $baseUrl . '/api/v1/public/categories'));
    expectSuccess('public.metrics', request('GET', $baseUrl . '/api/v1/public/platform-metrics'));
    expectSuccess(
        'public.partner-lead',
        request('POST', $baseUrl . '/api/v1/public/partner-leads', [
            'company_name' => 'Loja Smoke ' . $timestamp,
            'contact_name' => 'Contato Smoke',
            'email' => sprintf('lead-partner-%s@foxdelivery.com.br', $timestamp),
            'phone' => '+55 11 91111-0000',
            'city' => 'Sao Paulo',
            'business_type' => 'restaurant',
        ])
    );
    expectSuccess(
        'public.driver-lead',
        request('POST', $baseUrl . '/api/v1/public/driver-leads', [
            'full_name' => 'Motorista Smoke ' . $timestamp,
            'email' => sprintf('lead-driver-%s@foxdelivery.com.br', $timestamp),
            'phone' => '+55 11 92222-0000',
            'city' => 'Sao Paulo',
            'modal' => 'motorcycle',
        ])
    );

    writeLine('');
    writeLine('Smoke test concluido com sucesso.');
    writeLine(sprintf(
        'Resumo: health=%s, partner_store=%s, partner_orders=%d, admin_alerts=%d, partner_products=%d, driver_guard=%s',
        $health['status'] ?? 'ok',
        $partnerStore['store']['trade_name'] ?? '-',
        count($partnerOrders['orders'] ?? []),
        count($adminDashboard['alerts'] ?? []),
        count($partnerCatalog['products'] ?? []),
        $driverLogin['user']['guard'] ?? 'driver'
    ));
    exit(0);
} catch (Throwable $exception) {
    writeLine('[fail] ' . $exception->getMessage());
    exit(1);
}
