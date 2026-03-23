<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Controllers;

use FoxPlatform\Api\Application\Public\CreateDriverLead;
use FoxPlatform\Api\Application\Public\CreatePartnerLead;
use FoxPlatform\Api\Application\Public\CreatePublicOrder;
use FoxPlatform\Api\Application\Public\GetPlatformMetrics;
use FoxPlatform\Api\Application\Public\GetPublicCategories;
use FoxPlatform\Api\Application\Public\GetPublicStoreDetail;
use FoxPlatform\Api\Application\Public\GetPublicStores;
use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Interfaces\Http\Requests\DriverLeadCreateRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\PartnerLeadCreateRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\PublicOrderCreateRequest;
use FoxPlatform\Api\Interfaces\Http\Responses\ApiResponse;

class PublicLandingController
{
    public function __construct(
        private readonly GetPublicCategories $getPublicCategories,
        private readonly GetPlatformMetrics $getPlatformMetrics,
        private readonly GetPublicStores $getPublicStores,
        private readonly GetPublicStoreDetail $getPublicStoreDetail,
        private readonly CreatePublicOrder $createPublicOrder,
        private readonly CreatePartnerLead $createPartnerLead,
        private readonly CreateDriverLead $createDriverLead,
        private readonly PartnerLeadCreateRequest $partnerLeadCreateRequest,
        private readonly DriverLeadCreateRequest $driverLeadCreateRequest,
        private readonly PublicOrderCreateRequest $publicOrderCreateRequest
    ) {
    }

    public function categories(Request $request)
    {
        return ApiResponse::success(($this->getPublicCategories)());
    }

    public function metrics(Request $request)
    {
        return ApiResponse::success(($this->getPlatformMetrics)());
    }

    public function stores(Request $request)
    {
        return ApiResponse::success(($this->getPublicStores)([
            'city' => (string) $request->input('city', ''),
            'category' => (string) $request->input('category', ''),
            'search' => (string) $request->input('search', ''),
        ]));
    }

    public function storeDetail(Request $request)
    {
        return ApiResponse::success(
            ($this->getPublicStoreDetail)((string) $request->attribute('store_id'))
        );
    }

    public function createOrder(Request $request)
    {
        $validated = $this->publicOrderCreateRequest->validate($request);

        return ApiResponse::success(
            ($this->createPublicOrder)($validated),
            'Pedido criado com sucesso.'
        );
    }

    public function createPartnerLeadAction(Request $request)
    {
        $validated = $this->partnerLeadCreateRequest->validate($request);

        return ApiResponse::success(
            ($this->createPartnerLead)($validated),
            'Lead de parceiro registrado com sucesso.'
        );
    }

    public function createDriverLeadAction(Request $request)
    {
        $validated = $this->driverLeadCreateRequest->validate($request);

        return ApiResponse::success(
            ($this->createDriverLead)($validated),
            'Lead de entregador registrado com sucesso.'
        );
    }
}
