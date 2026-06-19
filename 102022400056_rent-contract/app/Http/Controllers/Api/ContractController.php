<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContractRequest;
use App\Http\Resources\ContractResource;
use App\Models\Contract;
use App\Services\AmqpPublisherService;
use App\Services\SoapAuditService;
use App\Services\SsoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

// ── Schema: Tenant (nested object) ──────────────────────────────────────────
#[OA\Schema(
    schema: 'TenantObject',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'),
        new OA\Property(property: 'name', type: 'string', example: 'Budi Santoso'),
        new OA\Property(property: 'email', type: 'string', example: 'budi@example.com'),
    ]
)]

// ── Schema: ContractResource ─────────────────────────────────────────────────
#[OA\Schema(
    schema: 'ContractResource',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'),
        new OA\Property(property: 'tenant_id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440001'),
        new OA\Property(property: 'listing_id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440002'),
        new OA\Property(property: 'start_date', type: 'string', format: 'date', example: '2024-01-01'),
        new OA\Property(property: 'end_date', type: 'string', format: 'date', example: '2024-12-31'),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(
            property: 'status',
            type: 'string',
            enum: ['DRAFT', 'ACTIVE', 'EXPIRED', 'TERMINATED'],
            example: 'DRAFT'
        ),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-01T00:00:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-01T00:00:00Z'),
        new OA\Property(property: 'tenant', ref: '#/components/schemas/TenantObject'),
    ]
)]

// ── Schema: ContractRequest (body for store/update) ──────────────────────────
#[OA\Schema(
    schema: 'ContractRequest',
    required: ['tenant_id', 'listing_id', 'start_date', 'end_date'],
    properties: [
        new OA\Property(property: 'tenant_id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440001'),
        new OA\Property(property: 'listing_id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440002'),
        new OA\Property(property: 'start_date', type: 'string', format: 'date', example: '2024-01-01'),
        new OA\Property(property: 'end_date', type: 'string', format: 'date', example: '2024-12-31'),
        new OA\Property(property: 'is_active', type: 'boolean', example: false),
        new OA\Property(
            property: 'status',
            type: 'string',
            enum: ['DRAFT', 'ACTIVE', 'EXPIRED', 'TERMINATED'],
            example: 'DRAFT'
        ),
    ]
)]

// ── Schema: ApiMeta ──────────────────────────────────────────────────────────
#[OA\Schema(
    schema: 'ApiMeta',
    properties: [
        new OA\Property(property: 'service_name', type: 'string', example: 'Rent-Contract-Service'),
        new OA\Property(property: 'api_version', type: 'string', example: 'v1'),
    ]
)]

// ── Schema: SuccessCollectionResponse ────────────────────────────────────────
#[OA\Schema(
    schema: 'SuccessCollectionResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'Data retrieved successfully'),
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ContractResource')
        ),
        new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
    ]
)]

// ── Schema: SuccessSingleResponse ────────────────────────────────────────────
#[OA\Schema(
    schema: 'SuccessSingleResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'Data retrieved successfully'),
        new OA\Property(property: 'data', ref: '#/components/schemas/ContractResource'),
        new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
    ]
)]

// ── Schema: ErrorResponse ────────────────────────────────────────────────────
#[OA\Schema(
    schema: 'ErrorResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Unable to process request'),
        new OA\Property(property: 'data', nullable: true, example: null),
        new OA\Property(property: 'meta', ref: '#/components/schemas/ApiMeta'),
    ]
)]

#[OA\Tag(name: 'Contracts', description: 'API Endpoints for managing contracts')]
class ContractController extends Controller
{

    // ==========================================
    // PRIVATE HELPER UNTUK SOAP & RABBITMQ
    // ==========================================
    private function dispatchAuditAndEvent(Contract $contract, string $activityName): void
    {
        $bearerToken = Cache::get('iae_m2m_token');

        if (! $bearerToken) {
            try {
                $bearerToken = app(SsoService::class)->loginM2M();
            } catch (\Exception $e) {
                Log::warning('[Contract] Gagal ambil M2M token, SOAP audit dilewati', [
                    'error' => $e->getMessage(),
                ]);
                return; // Berhenti jika gagal dapat token
            }
        }

        if ($bearerToken) {
            // ── SOAP Audit ──
            $receiptNumber = app(SoapAuditService::class)->auditContract($contract->toArray(), $bearerToken);

            if ($receiptNumber) {
                $contract->update([
                    'soap_receipt_number' => $receiptNumber,
                    'soap_audited_at'     => now(),
                ]);
            }

            // ── AMQP Publisher ──
            app(AmqpPublisherService::class)->publishViaHttp(
                $activityName,
                [
                    'activity_name' => $activityName,
                    'contract_id'   => $contract->id,
                    'tenant_id'     => $contract->tenant_id,
                    'listing_id'    => $contract->listing_id,
                    'receipt_ref'   => $receiptNumber ?? null,
                    'timestamp'     => now()->toIso8601String(),
                ],
                $bearerToken
            );
        }
    }

    #[OA\Get(
        path: '/api/v1/contract-service/contracts',
        summary: 'Get all contracts',
        security: [['bearerAuth' => [], 'apiKeyAuth' => []]],
        tags: ['Contracts'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Contracts retrieved successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/SuccessCollectionResponse')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    // ==========================================
    // GET /contracts (INDEX)
    // ==========================================
    public function index(): JsonResponse
    {
        $contracts = Contract::with('tenant')->get();

        foreach ($contracts as $contract) {
            $this->dispatchAuditAndEvent($contract, 'ContractListRetrieved');
        }

        return $this->successResponse(
            ContractResource::collection($contracts),
            'Data retrieved successfully',
            $this->apiMeta()
        );
    }

    #[OA\Post(
        path: '/api/v1/contract-service/contracts',
        summary: 'Create a new contract',
        security: [['bearerAuth' => [], 'apiKeyAuth' => []]],
        tags: ['Contracts'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ContractRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Contract created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/SuccessSingleResponse')
            ),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    // ==========================================
    // POST /contracts (STORE)
    // ==========================================
    public function store(StoreContractRequest $request): JsonResponse
    {
        $contract = Contract::create($request->validated());

        $this->dispatchAuditAndEvent($contract, 'ContractCreated');

        return $this->successResponse(
            new ContractResource($contract->load('tenant')),
            'Contract created successfully',
            $this->apiMeta()
        );
    }

    #[OA\Get(
        path: '/api/v1/contracts/{id}',
        summary: 'Get a specific contract',
        security: [['bearerAuth' => [], 'apiKeyAuth' => []]],
        tags: ['Contracts'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'UUID of the contract',
                schema: new OA\Schema(type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Contract retrieved successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/SuccessSingleResponse')
            ),
            new OA\Response(response: 404, description: 'Contract not found'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function show(Contract $contract): JsonResponse
    {
        $contract->load('tenant');

        return $this->successResponse(
            new ContractResource($contract),
            'Data retrieved successfully',
            $this->apiMeta()
        );
    }

    #[OA\Put(
        path: '/api/v1/contracts/{id}',
        summary: 'Update a contract',
        security: [['bearerAuth' => [], 'apiKeyAuth' => []]],
        tags: ['Contracts'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'UUID of the contract',
                schema: new OA\Schema(type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ContractRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Contract updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/SuccessSingleResponse')
            ),
            new OA\Response(
                response: 500,
                description: 'Failed to update',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(response: 404, description: 'Contract not found'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function update(StoreContractRequest $request, Contract $contract): JsonResponse
    {
        if (! $contract->update($request->validated())) {
            return $this->errorResponse('Unable to update contract', 500, null, $this->apiMeta());
        }

        return $this->successResponse(
            new ContractResource($contract->load('tenant')),
            'Contract updated successfully',
            $this->apiMeta()
        );
    }

    #[OA\Delete(
        path: '/api/v1/contracts/{id}',
        summary: 'Delete a contract',
        security: [['bearerAuth' => [], 'apiKeyAuth' => []]],
        tags: ['Contracts'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'UUID of the contract',
                schema: new OA\Schema(type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Contract deleted successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/SuccessSingleResponse')
            ),
            new OA\Response(
                response: 500,
                description: 'Failed to delete',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(response: 404, description: 'Contract not found'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function destroy(Contract $contract): JsonResponse
    {
        if (! $contract->delete()) {
            return $this->errorResponse('Unable to delete contract', 500, null, $this->apiMeta());
        }

        return $this->successResponse(
            new ContractResource($contract),
            'Contract deleted successfully',
            $this->apiMeta()
        );
    }

    private function apiMeta(): array
    {
        return [
            'service_name' => 'Rent-Contract-Service',
            'api_version' => 'v1',
        ];
    }
}
