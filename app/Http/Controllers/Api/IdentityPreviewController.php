<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\IdentityPreviewService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class IdentityPreviewController extends Controller
{
    use ApiResponse;

    public function __construct(
        private IdentityPreviewService $identityPreviewService
    ) {}

    public function preview(Request $request)
    {
        $request->validate([
            'id_front' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'id_back' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $result = $this->identityPreviewService->createPreview(
            $request->file('id_front'),
            $request->file('id_back')
        );

        return $this->successResponse(
            $result,
            'Identity preview generated successfully.'
        );
    }
}
