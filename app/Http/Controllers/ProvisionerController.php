<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProvisionerRequest;
use App\Support\Provisioning\CredentialProvisioner;
use App\Support\Provisioning\Dto\ProvisioningMeta;
use Illuminate\Http\JsonResponse;

class ProvisionerController extends Controller
{
    public function __construct(
        private readonly CredentialProvisioner $provisioner
    ) {
    }

    public function save(ProvisionerRequest $request): JsonResponse
    {
        $this->provisioner->apply(
            $request->validated(),
            ProvisioningMeta::make(
                user: $request->user(),
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            )
        );

        return response()->json([
            'status' => 'success',
            'message' => __('Settings updated successfully.'),
        ]);
    }
}
