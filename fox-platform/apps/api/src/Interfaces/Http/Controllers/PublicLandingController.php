<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Controllers;

use FoxPlatform\Api\Application\Public\CreateDriverLead;
use FoxPlatform\Api\Application\Public\CreatePartnerLead;
use FoxPlatform\Api\Application\Public\GetPlatformMetrics;
use FoxPlatform\Api\Application\Public\GetPublicCategories;
use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Interfaces\Http\Requests\DriverLeadCreateRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\PartnerLeadCreateRequest;
use FoxPlatform\Api\Interfaces\Http\Responses\ApiResponse;

class PublicLandingController
{
    public function __construct(
        private readonly GetPublicCategories $getPublicCategories,
        private readonly GetPlatformMetrics $getPlatformMetrics,
        private readonly CreatePartnerLead $createPartnerLead,
        private readonly CreateDriverLead $createDriverLead,
        private readonly PartnerLeadCreateRequest $partnerLeadCreateRequest,
        private readonly DriverLeadCreateRequest $driverLeadCreateRequest
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
