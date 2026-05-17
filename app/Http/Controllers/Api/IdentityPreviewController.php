<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IdentityPreviewRequest;
use App\Services\IdentityPreviewService;
use App\Traits\ApiResponse;

class IdentityPreviewController extends Controller
{
    use ApiResponse;

    public function __construct(
        private IdentityPreviewService $identityPreviewService
    ) {}

    public function preview(IdentityPreviewRequest $request)
    {
        $result = $this->identityPreviewService->createPreview(
            $request->resolvedIdFront(),
            $request->resolvedIdBack()
        );

        return $this->successResponse(
            $result,
            'Identity preview generated successfully.'
        );
    }
}
