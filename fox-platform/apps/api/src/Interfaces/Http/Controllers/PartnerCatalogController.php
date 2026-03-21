<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Controllers;

use FoxPlatform\Api\Application\Partner\CreatePartnerProduct;
use FoxPlatform\Api\Application\Partner\GetPartnerCatalog;
use FoxPlatform\Api\Application\Partner\UpdatePartnerProduct;
use FoxPlatform\Api\Application\Partner\UpdatePartnerInventory;
use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Interfaces\Http\Requests\PartnerInventoryUpdateRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\PartnerProductUpsertRequest;
use FoxPlatform\Api\Interfaces\Http\Responses\ApiResponse;

class PartnerCatalogController
{
    public function __construct(
        private readonly GetPartnerCatalog $getPartnerCatalog,
        private readonly CreatePartnerProduct $createPartnerProduct,
        private readonly UpdatePartnerProduct $updatePartnerProduct,
        private readonly UpdatePartnerInventory $updatePartnerInventory,
        private readonly PartnerProductUpsertRequest $partnerProductUpsertRequest,
        private readonly PartnerInventoryUpdateRequest $partnerInventoryUpdateRequest
    ) {
    }

    public function catalog(Request $request)
    {
        $auth = $request->attribute('auth');
        $catalog = ($this->getPartnerCatalog)($auth['user_id']);

        return ApiResponse::success($catalog);
    }

    public function createProduct(Request $request)
    {
        $auth = $request->attribute('auth');
        $validated = $this->partnerProductUpsertRequest->validate($request);
        $catalog = ($this->createPartnerProduct)($auth['user_id'], $validated);

        return ApiResponse::success($catalog, 'Produto cadastrado com sucesso.');
    }

    public function updateProduct(Request $request)
    {
        $auth = $request->attribute('auth');
        $validated = $this->partnerProductUpsertRequest->validate($request);
        $catalog = ($this->updatePartnerProduct)($auth['user_id'], (string) $request->attribute('product_id'), $validated);

        return ApiResponse::success($catalog, 'Produto atualizado com sucesso.');
    }

    public function updateInventory(Request $request)
    {
        $auth = $request->attribute('auth');
        $validated = $this->partnerInventoryUpdateRequest->validate($request);
        $catalog = ($this->updatePartnerInventory)($auth['user_id'], (string) $request->attribute('product_id'), $validated);

        return ApiResponse::success($catalog, 'Estoque atualizado com sucesso.');
    }
}
