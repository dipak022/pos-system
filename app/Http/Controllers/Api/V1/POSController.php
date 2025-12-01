<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\POS\ProcessSaleRequest;
use App\Services\POSService;
use Illuminate\Http\JsonResponse;
use App\Helpers\ApiResponse;

class POSController extends Controller
{
    private POSService $posService;

    public function __construct(POSService $posService)
    {
        $this->posService = $posService;
    }

    /**
     * Process a sale
     *
     * @param ProcessSaleRequest $request
     * @return JsonResponse
     */
    public function processSale(ProcessSaleRequest $request): JsonResponse
    {
        $result = $this->posService->processSale(
            auth()->id(),
            $request->input('items')
        );

        return response()->json($result, 201);
        //return ApiResponse::success('Token refreshed', $result);
    }
}
