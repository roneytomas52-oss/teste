<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Controllers;

use FoxPlatform\Api\Application\Partner\AddPartnerStoreDocument;
use FoxPlatform\Api\Application\Partner\GetPartnerProfile;
use FoxPlatform\Api\Application\Partner\GetPartnerStore;
use FoxPlatform\Api\Application\Partner\ReplacePartnerStoreHours;
use FoxPlatform\Api\Application\Partner\UpdatePartnerProfile;
use FoxPlatform\Api\Application\Partner\UpdatePartnerStore;
use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Interfaces\Http\Requests\PartnerProfileUpdateRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\PartnerStoreDocumentRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\PartnerStoreHoursRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\PartnerStoreUpdateRequest;
use FoxPlatform\Api\Interfaces\Http\Responses\ApiResponse;

class PartnerController
{
    public function __construct(
        private readonly GetPartnerProfile $getPartnerProfile,
        private readonly UpdatePartnerProfile $updatePartnerProfile,
        private readonly GetPartnerStore $getPartnerStore,
        private readonly UpdatePartnerStore $updatePartnerStore,
        private readonly ReplacePartnerStoreHours $replacePartnerStoreHours,
        private readonly AddPartnerStoreDocument $addPartnerStoreDocument,
        private readonly PartnerProfileUpdateRequest $partnerProfileUpdateRequest,
        private readonly PartnerStoreUpdateRequest $partnerStoreUpdateRequest,
        private readonly PartnerStoreHoursRequest $partnerStoreHoursRequest,
        private readonly PartnerStoreDocumentRequest $partnerStoreDocumentRequest
    ) {
    }

    public function profile(Request $request)
    {
        $auth = $request->attribute('auth');
        $profile = ($this->getPartnerProfile)($auth['user_id']);

        return ApiResponse::success($profile);
    }

    public function updateProfile(Request $request)
    {
        $auth = $request->attribute('auth');
        $validated = $this->partnerProfileUpdateRequest->validate($request);
        $profile = ($this->updatePartnerProfile)($auth['user_id'], $validated);

        return ApiResponse::success($profile, 'Perfil do parceiro atualizado com sucesso.');
    }

    public function store(Request $request)
    {
        $auth = $request->attribute('auth');
        $store = ($this->getPartnerStore)($auth['user_id']);

        return ApiResponse::success($store);
    }

    public function updateStore(Request $request)
    {
        $auth = $request->attribute('auth');
        $validated = $this->partnerStoreUpdateRequest->validate($request);
        $store = ($this->updatePartnerStore)($auth['user_id'], $validated);

        return ApiResponse::success($store, 'Dados da loja atualizados com sucesso.');
    }

    public function updateStoreHours(Request $request)
    {
        $auth = $request->attribute('auth');
        $validated = $this->partnerStoreHoursRequest->validate($request);
        $hours = ($this->replacePartnerStoreHours)($auth['user_id'], $validated['hours']);

        return ApiResponse::success($hours, 'Horarios da loja atualizados com sucesso.');
    }

    public function addStoreDocument(Request $request)
    {
        $auth = $request->attribute('auth');
        $validated = $this->partnerStoreDocumentRequest->validate($request);
        $document = ($this->addPartnerStoreDocument)($auth['user_id'], $validated);

        return ApiResponse::success($document, 'Documento da loja registrado com sucesso.', [], 201);
    }
}
